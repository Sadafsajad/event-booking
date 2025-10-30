@component('mail::message')
# Booking Confirmed 🎉

You’ve successfully booked **{{ $event }}** at **{{ $venue }}**.

**Seats:** {{ $qty }}
**Date:** {{ \Carbon\Carbon::parse($date)->toDayDateTimeString() }}

Thanks for booking with us!
@endcomponent