<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public string $summary) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Update for order '.$this->order->number);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.order-status');
    }
}
