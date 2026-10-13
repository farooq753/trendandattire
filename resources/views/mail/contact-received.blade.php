<x-mail::message>
# {{ $contact->subject }}

From {{ $contact->name }} ({{ $contact->email }})

{{ $contact->message }}
</x-mail::message>
