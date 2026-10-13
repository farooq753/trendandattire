<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function verifyBank(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate(['staff_note' => ['nullable', 'string', 'max:500']]);

        try {
            $payments->verifyBankTransfer($payment, $data['staff_note'] ?? null);
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Bank transfer marked paid.');
    }

    public function rejectBank(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate(['staff_note' => ['nullable', 'string', 'max:500']]);

        try {
            $payments->rejectBankEvidence($payment, $data['staff_note'] ?? null);
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Evidence rejected. The payment is still unpaid.');
    }

    public function collectCod(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate(['staff_note' => ['nullable', 'string', 'max:500']]);

        try {
            $payments->confirmCodCollection($payment, $data['staff_note'] ?? null);
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Cash on delivery marked collected.');
    }

    public function refund(Request $request, Payment $payment, PaymentService $payments): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $payments->recordRefund($payment, (string) $data['amount'], $data['reason']);
        } catch (CheckoutException $exception) {
            return back()->withErrors($exception->errors);
        }

        return back()->with('status', 'Refund recorded. This does not call a live payment processor.');
    }
}
