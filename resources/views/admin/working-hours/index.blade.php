@extends('layouts.app')
@section('title', 'Working Hours')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">Working Hours</h2>
    <span class="text-xs text-gray-500">Used by TAT to pause SLA clocks outside business hours.</span>
</div>

<form method="POST" action="{{ route('admin.working-hours.update') }}" class="bg-white rounded-lg shadow overflow-hidden">
    @csrf
    @method('PATCH')
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Day</th>
                <th class="px-4 py-3 text-left">Working Day</th>
                <th class="px-4 py-3 text-left">Start</th>
                <th class="px-4 py-3 text-left">End</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($hours as $i => $h)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-700">
                    {{ \App\Models\WorkingHour::DAY_NAMES[$h->day_of_week] }}
                    <input type="hidden" name="days[{{ $i }}][day_of_week]" value="{{ $h->day_of_week }}">
                </td>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="days[{{ $i }}][is_working_day]" value="1"
                               class="rounded border-gray-300" {{ $h->is_working_day ? 'checked' : '' }}>
                        <span class="text-xs text-gray-500">Open</span>
                    </label>
                </td>
                <td class="px-4 py-3">
                    <input type="time" name="days[{{ $i }}][start_time]"
                           value="{{ \Carbon\Carbon::createFromTimeString((string) $h->start_time)->format('H:i') }}"
                           class="border border-gray-300 rounded px-2 py-1 text-sm">
                </td>
                <td class="px-4 py-3">
                    <input type="time" name="days[{{ $i }}][end_time]"
                           value="{{ \Carbon\Carbon::createFromTimeString((string) $h->end_time)->format('H:i') }}"
                           class="border border-gray-300 rounded px-2 py-1 text-sm">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3 border-t bg-gray-50 text-right">
        <button type="submit" class="text-white px-4 py-2 rounded text-sm" style="background:#0056B3;">Save Working Hours</button>
    </div>
</form>
@endsection
