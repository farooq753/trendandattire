<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storefront\ContactRequest;
use App\Mail\ContactReceivedMail;
use App\Models\AttributeValue;
use App\Models\ContactMessage;
use App\Models\Country;
use App\Models\Currency;
use App\Services\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function sendContact(ContactRequest $request, StoreSettings $settings): RedirectResponse
    {
        $message = ContactMessage::query()->create($request->validated());
        $to = (string) $settings->get('support_email', config('mail.from.address'));
        Mail::to($to)->queue((new ContactReceivedMail($message))->afterCommit());

        return back()->with('status', 'Message sent. We will reply from the store support address.');
    }

    public function faq(): View
    {
        return view('pages.faq');
    }

    public function shipping(): View
    {
        return view('pages.shipping');
    }

    public function returns(): View
    {
        return view('pages.returns');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function sizeGuide(): View
    {
        $sizes = AttributeValue::query()
            ->whereHas('attribute', fn ($query) => $query->where('type', 'size'))
            ->orderBy('sort_order')
            ->get();

        return view('pages.size-guide', ['sizes' => $sizes]);
    }

    public function measurementGuide(): View
    {
        return view('pages.measurement-guide');
    }

    public function preferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['nullable', 'in:en'],
            'currency_code' => ['nullable', 'exists:currencies,code'],
            'country_code' => ['nullable', 'exists:countries,code'],
        ]);

        if (! empty($data['currency_code']) && ! Currency::query()->where('code', $data['currency_code'])->where('is_active', true)->exists()) {
            return back()->withErrors(['currency_code' => 'That currency is not available.']);
        }

        if (! empty($data['country_code']) && ! Country::query()->where('code', $data['country_code'])->where('is_active', true)->exists()) {
            return back()->withErrors(['country_code' => 'That country is not available.']);
        }

        foreach (['locale', 'currency_code', 'country_code'] as $key) {
            if (! empty($data[$key])) {
                session([$key => $data[$key]]);
            }
        }

        return back();
    }
}
