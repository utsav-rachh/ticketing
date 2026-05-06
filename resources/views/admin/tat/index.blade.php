@extends('layouts.app')
@section('title', 'TAT Configuration')
@section('content')
<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
    <h2 class="text-lg md:text-xl font-bold text-gray-700">TAT Configuration</h2>
    <span class="text-xs text-gray-500">SLA budgets are per-status and burn only during working hours.</span>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Transition</th>
                <th class="px-4 py-3 text-left">TAT (working hours)</th>
                <th class="px-4 py-3 text-left">Warning at (%)</th>
                <th class="px-4 py-3 text-left">Active</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($configs as $c)
            @php $editable = in_array($c->status, \App\Models\TatConfiguration::SLA_STATUSES, true); @endphp
            <tr class="hover:bg-gray-50 {{ !$editable ? 'opacity-60' : '' }}">
                <form method="POST" action="{{ route('admin.tat.update', $c) }}">
                    @csrf
                    @method('PATCH')
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs
                            @switch($c->status)
                                @case('open')        bg-blue-100 text-blue-700 @break
                                @case('in_progress') bg-yellow-100 text-yellow-700 @break
                                @case('reopen')      bg-pink-100 text-pink-700 @break
                                @case('hold')        bg-purple-100 text-purple-700 @break
                                @case('resolved')    bg-green-100 text-green-700 @break
                                @case('closed')      bg-gray-100 text-gray-700 @break
                                @case('pending_info')bg-orange-100 text-orange-700 @break
                                @default            bg-gray-100 text-gray-700
                            @endswitch">
                            {{ ucfirst(str_replace('_',' ', $c->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 font-mono">{{ $c->applies_to_transition ?: '—' }}</td>
                    <td class="px-4 py-3">
                        @if($editable)
                        <input type="number" step="0.5" min="0.5" name="tat_hours" value="{{ $c->tat_hours }}"
                               class="w-24 border rounded px-2 py-1 text-sm" required>
                        @else
                        <span class="text-xs text-gray-400 italic">no clock</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($editable)
                        <input type="number" min="1" max="99" name="warning_threshold_pct" value="{{ $c->warning_threshold_pct }}"
                               class="w-24 border rounded px-2 py-1 text-sm" required>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($editable)
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" {{ $c->is_active ? 'checked' : '' }}>
                            <span class="text-xs text-gray-500">Active</span>
                        </label>
                        @else
                        <span class="text-xs text-gray-400">n/a</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($editable)
                        <button type="submit" class="text-white px-3 py-1 rounded text-xs font-medium" style="background:#0056B3;">Save</button>
                        @endif
                    </td>
                </form>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No TAT configurations found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
