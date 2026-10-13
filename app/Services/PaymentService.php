<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\CheckoutException;
use App\Exceptions\InvalidWebhookSignature;
use App\Models\OrderEvent;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Refund;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private StripeGateway $stripe,
        private PayPalGateway $paypal,
        private InventoryService $inventory,
        private AuditLogger $audit,
        private OrderNotifier $notifier,
    ) {}

    public function submitBankEvidence(Payment $payment, string $reference, ?string $path, ?string $note): void
    {
        if ($payment->method !== 'bank_transfer' || ! in_array($payment->status, ['awaiting_payment', 'evidence_submitted'], true)) {
            throw new CheckoutException(['payment' => 'This payment is not waiting for a bank transfer.']);
        }

        DB::transaction(function () use ($payment, $reference, $path, $note) {
            $before = $payment->status;
            $payment->status = PaymentStatus::EvidenceSubmitted->value;
            $payment->bank_reference = $reference;
            $payment->evidence_path = $path ?: $payment->evidence_path;
            $payment->customer_note = $note;
            $payment->save();
            $payment->order->payment_status = PaymentStatus::EvidenceSubmitted->value;
            $payment->order->save();
            $this->event($payment, 'bank_evidence_submitted', $before, $payment->status, ['reference' => $reference]);
            OrderEvent::query()->create([
                'order_id' => $payment->order_id,
                'type' => 'bank_evidence',
                'message' => 'Bank transfer reference submitted. Payment remains unpaid until staff verify it.',
                'user_id' => auth()->id(),
            ]);
        });
    }

    public function verifyBankTransfer(Payment $payment, ?string $note = null): void
    {
        if ($payment->method !== 'bank_transfer' || $payment->status !== PaymentStatus::EvidenceSubmitted->value) {
            throw new CheckoutException(['payment' => 'Only a submitted bank transfer can be verified.']);
        }

        DB::transaction(function () use ($payment, $note) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (! $this->inventory->consumeReservations($payment->order)) {
                throw new CheckoutException(['stock' => 'Reserved stock is no longer available, so this transfer was not marked paid.']);
            }

            $before = $payment->status;
            $payment->status = PaymentStatus::Paid->value;
            $payment->staff_note = $note;
            $payment->verified_by = auth()->id();
            $payment->verified_at = now();
            $payment->save();
            $order = $payment->order;
            $order->payment_status = PaymentStatus::Paid->value;
            $order->save();
            $this->event($payment, 'bank_verified', $before, $payment->status, null);
            $this->audit->log('payment.bank_verified', $payment, ['status' => $before], ['status' => $payment->status]);
            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'payment_paid',
                'message' => 'Bank transfer verified by staff.',
                'user_id' => auth()->id(),
            ]);
        });

        $this->notifier->status($payment->order->fresh(), 'Your bank transfer was verified.');
    }

    public function rejectBankEvidence(Payment $payment, ?string $note = null): void
    {
        if ($payment->status !== PaymentStatus::EvidenceSubmitted->value) {
            throw new CheckoutException(['payment' => 'There is no transfer evidence to reject.']);
        }

        $before = $payment->status;
        $payment->status = PaymentStatus::AwaitingPayment->value;
        $payment->staff_note = $note;
        $payment->save();
        $payment->order->payment_status = PaymentStatus::AwaitingPayment->value;
        $payment->order->save();
        $this->event($payment, 'bank_evidence_rejected', $before, $payment->status, null);
        $this->audit->log('payment.bank_rejected', $payment, ['status' => $before], ['status' => $payment->status]);
    }

    public function confirmCodCollection(Payment $payment, ?string $note = null): void
    {
        if ($payment->method !== 'cod' || $payment->status !== PaymentStatus::Pending->value) {
            throw new CheckoutException(['payment' => 'Cash on delivery can only be confirmed while it is unpaid.']);
        }

        $before = $payment->status;
        $payment->status = PaymentStatus::Paid->value;
        $payment->staff_note = $note;
        $payment->verified_by = auth()->id();
        $payment->verified_at = now();
        $payment->save();
        $payment->order->payment_status = PaymentStatus::Paid->value;
        $payment->order->save();
        $this->event($payment, 'cod_collected', $before, $payment->status, null);
        $this->audit->log('payment.cod_collected', $payment, ['status' => $before], ['status' => $payment->status]);
        OrderEvent::query()->create([
            'order_id' => $payment->order_id,
            'type' => 'payment_paid',
            'message' => 'Cash on delivery collected.',
            'user_id' => auth()->id(),
        ]);
        $this->notifier->status($payment->order, 'Cash on delivery was collected.');
    }

    public function handleStripe(Request $request): void
    {
        $event = $this->stripe->verifiedEvent($request);
        $intentId = $event['data']['object']['id'] ?? null;
        $eventId = $event['id'] ?? null;

        if (! is_string($intentId)) {
            return;
        }

        $remote = $this->stripe->retrievePaymentIntent($intentId);

        if ($remote === null) {
            throw new InvalidWebhookSignature('Stripe payment status could not be confirmed.');
        }

        $this->applyProviderStatus('stripe', $intentId, is_string($eventId) ? $eventId : null, (string) ($remote['status'] ?? ''), [
            'stripe_status' => $remote['status'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handlePayPal(Request $request): void
    {
        $verified = $this->paypal->verifiedCapture($request);
        $capture = $verified['capture'];
        $eventId = $verified['event']['id'] ?? null;
        $captureId = $capture['id'] ?? null;

        if (! is_string($captureId)) {
            return;
        }

        $this->applyProviderStatus('paypal', $captureId, is_string($eventId) ? $eventId : null, (string) ($capture['status'] ?? ''), [
            'paypal_status' => $capture['status'] ?? null,
        ]);
    }

    public function recordRefund(Payment $payment, string $amount, string $reason): Refund
    {
        return DB::transaction(function () use ($payment, $amount, $reason) {
            $payment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $already = (string) $payment->refunds()->whereIn('status', ['approved', 'processed'])->sum('amount');
            $next = Money::add($already, $amount);

            if (! in_array($payment->status, [PaymentStatus::Paid->value, PaymentStatus::PartiallyRefunded->value], true)) {
                throw new CheckoutException(['refund' => 'Only a collected payment can be refunded.']);
            }

            if (Money::compare($next, (string) $payment->amount) > 0) {
                throw new CheckoutException(['refund' => 'The refund is larger than the amount collected.']);
            }

            $refund = Refund::query()->create([
                'order_id' => $payment->order_id,
                'payment_id' => $payment->id,
                'amount' => Money::round($amount, 4),
                'currency_code' => $payment->currency_code,
                'status' => 'processed',
                'reason' => $reason,
                'processed_by' => auth()->id(),
            ]);

            $status = Money::compare($next, (string) $payment->amount) === 0
                ? PaymentStatus::Refunded
                : PaymentStatus::PartiallyRefunded;
            $before = $payment->status;
            $payment->status = $status->value;
            $payment->save();
            $payment->order->payment_status = $status->value;
            $payment->order->save();
            $this->audit->log('refund.processed', $refund, ['payment_status' => $before], ['payment_status' => $status->value, 'amount' => $refund->amount]);

            return $refund;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyProviderStatus(string $provider, string $reference, ?string $eventId, string $remoteStatus, array $payload): void
    {
        $payment = Payment::query()->where('provider', $provider)->where('provider_reference', $reference)->first();

        if (! $payment) {
            return;
        }

        if ($eventId && PaymentEvent::query()->where('payment_id', $payment->id)->where('provider_event_id', $eventId)->exists()) {
            return;
        }

        $mapped = match (strtoupper($remoteStatus)) {
            'SUCCEEDED', 'COMPLETED' => PaymentStatus::Paid,
            'CANCELED', 'CANCELLED', 'DENIED', 'VOIDED' => PaymentStatus::Cancelled,
            'REQUIRES_CAPTURE', 'APPROVED' => PaymentStatus::Authorized,
            'FAILED', 'DECLINED' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };

        if ($mapped === PaymentStatus::Paid && ! in_array($payment->status, [PaymentStatus::Pending->value, PaymentStatus::Authorized->value, PaymentStatus::AwaitingPayment->value], true)) {
            $this->event($payment, $provider.'.ignored', $payment->status, $payment->status, $payload, $eventId);

            return;
        }

        DB::transaction(function () use ($payment, $mapped, $payload, $eventId, $provider) {
            $before = $payment->status;

            if ($mapped === PaymentStatus::Paid) {
                $this->inventory->consumeReservations($payment->order);
            }

            if (in_array($mapped, [PaymentStatus::Failed, PaymentStatus::Cancelled], true)) {
                $this->inventory->releaseOrderReservations($payment->order, 'released');
            }

            $payment->status = $mapped->value;
            $payment->save();
            $payment->order->payment_status = $mapped->value;
            $payment->order->save();
            $this->event($payment, $provider.'.status', $before, $mapped->value, $payload, $eventId);
            $this->audit->log('payment.'.$provider, $payment, ['status' => $before], ['status' => $mapped->value]);
        });
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function event(Payment $payment, string $type, ?string $before, ?string $after, ?array $payload, ?string $eventId = null): void
    {
        PaymentEvent::query()->create([
            'payment_id' => $payment->id,
            'provider_event_id' => $eventId,
            'type' => $type,
            'status_before' => $before,
            'status_after' => $after,
            'payload' => $payload,
        ]);
    }
}
