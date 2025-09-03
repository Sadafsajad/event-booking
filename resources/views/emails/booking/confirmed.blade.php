@component('mail::message')
# Booking Confirmed

Hi {{ $user->name }},

**Event:** {{ $event->title }}
**Venue:** {{ $event->venue }}
**When:** {{ $event->event_at->format('d M Y, h:i A') }}
**Tickets:** {{ $qty }}

Thanks,
{{ config('app.name') }}
@endcomponent