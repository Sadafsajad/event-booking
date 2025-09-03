<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight:600;font-size:20px;">Reports</h2>
    </x-slot>

    <div style="padding:16px 0;max-width:1100px;margin:0 auto;display:grid;gap:16px;">
        {{-- Top 5 events last 30 days --}}
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px;">
            <h3 style="margin-bottom:10px;font-weight:700;">Top 5 Events (Last 30 days)</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <th style="text-align:left;padding:8px;">Title</th>
                            <th style="text-align:left;padding:8px;">Venue</th>
                            <th style="text-align:left;padding:8px;">Date</th>
                            <th style="text-align:left;padding:8px;">Booked (30d)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($top5 as $row)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:8px;">{{ $row->title }}</td>
                                <td style="padding:8px;">{{ $row->venue }}</td>
                                <td style="padding:8px;">{{ \Carbon\Carbon::parse($row->event_at)->format('Y-m-d H:i') }}
                                </td>
                                <td style="padding:8px;">{{ (int) ($row->booked_last30 ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding:10px;color:#6b7280;">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Power users last month --}}
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px;">
            <h3 style="margin-bottom:10px;font-weight:700;">Users with &gt;3 Events (Last Month)</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <th style="text-align:left;padding:8px;">Name</th>
                            <th style="text-align:left;padding:8px;">Email</th>
                            <th style="text-align:left;padding:8px;">#Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($powerUsers as $row)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:8px;">{{ $row->name }}</td>
                                <td style="padding:8px;">{{ $row->email }}</td>
                                <td style="padding:8px;">{{ (int) $row->events_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="padding:10px;color:#6b7280;">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Occupancy --}}
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px;">
            <h3 style="margin-bottom:10px;font-weight:700;">Occupancy (All Events)</h3>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <th style="text-align:left;padding:8px;">Title</th>
                            <th style="text-align:left;padding:8px;">Capacity</th>
                            <th style="text-align:left;padding:8px;">Booked</th>
                            <th style="text-align:left;padding:8px;">Occupancy %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($occupancy as $row)
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:8px;">{{ $row->title }}</td>
                                <td style="padding:8px;">{{ (int) $row->capacity }}</td>
                                <td style="padding:8px;">{{ (int) ($row->booked ?? 0) }}</td>
                                <td style="padding:8px;">{{ number_format((float) $row->occupancy_percent, 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding:10px;color:#6b7280;">No data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>