<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <h2 style="font-weight:600;font-size:20px;">Events</h2>

            @if(auth()->check() && auth()->user()->is_admin)
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('admin.reports.dashboard') }}"
                        style="padding:8px 12px;border-radius:8px;background:#111827;color:#fff;text-decoration:none;font-weight:600;">
                        Reports
                    </a>
                    <a href="{{ route('admin.events.create') }}"
                        style="padding:8px 12px;border-radius:8px;background:#4f46e5;color:#fff;text-decoration:none;font-weight:600;">
                        + Create
                    </a>
                </div>
            @endif
        </div>
    </x-slot>

    <div style="padding:16px 0;">
        <div style="max-width:1100px;margin:0 auto;">

            {{-- flash success --}}
            @if (session('status'))
                <div
                    style="margin-bottom:12px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:10px 12px;border-radius:8px;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- validation error from booking (qty/full) --}}
            @if ($errors->has('qty'))
                <div
                    style="margin-bottom:12px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:10px 12px;border-radius:8px;">
                    {{ $errors->first('qty') }}
                </div>
            @endif

            <div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px;">
                <form method="GET" action="{{ url(request()->is('admin/*') ? '/admin/events' : '/events') }}"
                    style="display:grid;grid-template-columns:1fr 170px 170px 120px;gap:8px;margin-bottom:12px;">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title or venue"
                        style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                    <input type="date" name="from" value="{{ request('from') }}"
                        style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                    <input type="date" name="to" value="{{ request('to') }}"
                        style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                    <button style="border:none;border-radius:8px;background:#111827;color:#fff;font-weight:700;">
                        Filter
                    </button>
                </form>

                <div style="overflow-x:auto;">
                    <table style="width:100%;font-size:14px;border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:1px solid #e5e7eb;">
                                <th style="text-align:left;padding:10px;">Title</th>
                                <th style="text-align:left;padding:10px;">Venue</th>
                                <th style="text-align:left;padding:10px;">Capacity</th>
                                <th style="text-align:left;padding:10px;">Date</th>
                                <th style="text-align:left;padding:10px;">Book</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // maps passed from controller
                                $bookedMap = $bookedMap ?? collect();
                                $mineMap = $mineMap ?? collect();
                            @endphp

                            @forelse($events as $e)
                                @php
                                    $booked = (int) ($bookedMap[$e->id] ?? 0);
                                    $left = max((int) $e->capacity - $booked, 0);
                                    $iBookedQty = (int) ($mineMap[$e->id] ?? 0);
                                @endphp
                                <tr style="border-bottom:1px solid #f3f4f6;">
                                    <td style="padding:10px;">{{ $e->title }}</td>
                                    <td style="padding:10px;">{{ $e->venue }}</td>
                                    <td style="padding:10px;">{{ $e->capacity }}</td>
                                    <td style="padding:10px;">{{ \Carbon\Carbon::parse($e->event_at)->format('Y-m-d H:i') }}
                                    </td>

                                    {{-- Book / Status column --}}
                                    <td style="padding:10px;">
                                        @if(auth()->check() && auth()->user()->is_admin)
                                            <span style="color:#374151;">{{ $booked }}/{{ $e->capacity }} booked</span>
                                        @else
                                            @if($left === 0)
                                                <span
                                                    style="display:inline-block;padding:6px 10px;border-radius:8px;background:#fee2e2;color:#991b1b;font-weight:600;">Full</span>
                                            @elseif($iBookedQty > 0)
                                                <span
                                                    style="display:inline-block;padding:6px 10px;border-radius:8px;background:#e0e7ff;color:#3730a3;font-weight:600;">Booked
                                                    ({{ $iBookedQty }})</span>
                                            @else
                                                <form method="POST" action="{{ route('bookings.store', $e->id) }}"
                                                    style="display:flex;gap:8px;align-items:center;">
                                                    @csrf
                                                    <input type="number" name="qty" value="1" min="1"
                                                        style="width:70px;border:1px solid #e5e7eb;border-radius:8px;padding:6px;">
                                                    <button
                                                        style="border:none;border-radius:8px;background:#16a34a;color:#fff;padding:6px 10px;font-weight:600;">
                                                        Book ({{ $left }} left)
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding:14px;text-align:center;color:#6b7280;">No events</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:12px;">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>