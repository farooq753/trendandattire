<?php

namespace App\Services;

use App\Mail\OrderPlacedMail;
use App\Mail\OrderStatusMail;
use App\Models\EmailLog;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderNotifier
{
    public function placed(Order $order): void
    {
        $this->queue(new OrderPlacedMail($order), $order->customer_email, $order);
    }

    public function status(Order $order, string $summary): void
    {
        $this->queue(new OrderStatusMail($order, $summary), $order->customer_email, $order);
    }

    private function queue(OrderPlacedMail|OrderStatusMail $mailable, string $recipient, Order $order): void
    {
        Mail::to($recipient)->queue($mailable->afterCommit());

        EmailLog::query()->create([
            'mailable' => $mailable::class,
            'recipient' => $recipient,
            'subject' => null,
            'status' => 'queued',
            'related_type' => $order->getMorphClass(),
            'related_id' => $order->id,
        ]);
    }
}
