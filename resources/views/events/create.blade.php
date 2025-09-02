<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight:600;font-size:20px;">Create Event</h2>
    </x-slot>

    <div style="padding:16px 0;">
        <div style="max-width:900px;margin:0 auto;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 1px 2px rgba(0,0,0,.05);padding:16px;">
                @if (session('status'))
                    <div style="margin-bottom:10px;color:green;">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.events.store') }}"
                    style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    @csrf

                    <div>
                        <label>Title</label>
                        <input name="title" required
                            style="display:block;width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-top:6px;">
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <div>
                        <label>Venue</label>
                        <input name="venue" required
                            style="display:block;width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-top:6px;">
                        <x-input-error :messages="$errors->get('venue')" class="mt-1" />
                    </div>

                    <div>
                        <label>Capacity</label>
                        <input type="number" name="capacity" min="1" required
                            style="display:block;width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-top:6px;">
                        <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
                    </div>

                    <div>
                        <label>Date &amp; Time</label>
                        <input type="datetime-local" name="event_at" required
                            style="display:block;width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-top:6px;">
                        <x-input-error :messages="$errors->get('event_at')" class="mt-1" />
                    </div>

                    <div style="grid-column:1 / -1">
                        <label>Description (optional)</label>
                        <textarea name="description" rows="3"
                            style="display:block;width:100%;border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-top:6px;"></textarea>
                    </div>

                    <div style="grid-column:1 / -1;display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
                        <a href="{{ route('admin.events.index') }}"
                            style="color:#6b7280;text-decoration:underline;">Back</a>
                        <button
                            style="border:none;border-radius:8px;background:#4f46e5;color:#fff;font-weight:700;padding:10px 16px;">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>