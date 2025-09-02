<x-app-layout>
    <x-slot name="header">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
            <h2 style="font-weight:600;font-size:20px;">Events</h2>
            @if(auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('admin.events.create') }}"
                    style="padding:8px 12px;border-radius:8px;background:#4f46e5;color:#fff;text-decoration:none;font-weight:600;">
                    + Create
                </a>
            @endif
        </div>
    </x-slot>

    <div style="padding:16px 0;">
        <div style="max-width:1100px;margin:0 auto;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px;">
                <form method="GET" action="{{ url('/events') }}"
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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $e)
                                <tr style="border-bottom:1px solid #f3f4f6;">
                                    <td style="padding:10px;">{{ $e->title }}</td>
                                    <td style="padding:10px;">{{ $e->venue }}</td>
                                    <td style="padding:10px;">{{ $e->capacity }}</td>
                                    <td style="padding:10px;">
                                        {{ \Carbon\Carbon::parse($e->event_at)->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding:14px;text-align:center;color:#6b7280;">No events</td>
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