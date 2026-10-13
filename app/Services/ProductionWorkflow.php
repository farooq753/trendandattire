<?php

namespace App\Services;

use App\Enums\ProductionStatus;
use App\Exceptions\CheckoutException;
use App\Models\OrderEvent;
use App\Models\ProductionJob;
use Illuminate\Support\Facades\DB;

class ProductionWorkflow
{
    public function __construct(private AuditLogger $audit, private OrderNotifier $notifier) {}

    public function transition(ProductionJob $job, string $status, ?string $notes = null): void
    {
        $current = ProductionStatus::from($job->status);
        $next = ProductionStatus::tryFrom($status);

        if (! $next || ! in_array($next->value, $current->allowedNext(), true)) {
            throw new CheckoutException(['status' => 'That production status change is not allowed.']);
        }

        DB::transaction(function () use ($job, $current, $next, $notes) {
            $job->status = $next->value;
            $job->staff_notes = trim(($job->staff_notes ? $job->staff_notes."\n" : '').($notes ?? ''));
            $job->updated_by = auth()->id();
            $job->save();
            $job->orderItem->production_status = $next->value;
            $job->orderItem->save();
            OrderEvent::query()->create([
                'order_id' => $job->orderItem->order_id,
                'type' => 'production',
                'message' => 'Production status: '.$next->label().'.',
                'meta' => ['notes' => $notes],
                'user_id' => auth()->id(),
            ]);
            $this->audit->log('production.transition', $job, ['status' => $current->value], ['status' => $next->value]);
        });

        $this->notifier->status($job->orderItem->order, 'Production update: '.$next->label().'.');
    }
}
