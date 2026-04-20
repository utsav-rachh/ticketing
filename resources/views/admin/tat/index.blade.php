@extends('layouts.app')
@section('title', 'TAT Configuration')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">TAT Configuration</h2>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Priority</th>
                <th class="px-4 py-3 text-left">TAT (hours)</th>
                <th class="px-4 py-3 text-left">Warning at (%)</th>
                <th class="px-4 py-3 text-left">Escalate to</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($configs as $c)
            <tr class="hover:bg-gray-50">
                <form method="POST" action="{{ route('admin.tat.update', $c) }}">
                    @csrf
                    @method('PATCH')
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs
                            @switch($c->priority)
                                @case('critical') bg-red-100 text-red-700 @break
                                @case('high')     bg-orange-100 text-orange-700 @break
                                @case('medium')   bg-yellow-100 text-yellow-700 @break
                                @case('low')      bg-green-100 text-green-700 @break
                                @default         bg-gray-100 text-gray-700
                            @endswitch">
                            {{ ucfirst($c->priority) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" step="0.5" min="0.5" name="tat_hours" value="{{ $c->tat_hours }}"
                               class="w-24 border rounded px-2 py-1 text-sm" required>
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" min="1" max="99" name="warning_threshold_pct" value="{{ $c->warning_threshold_pct }}"
                               class="w-24 border rounded px-2 py-1 text-sm" required>
                    </td>
                    <td class="px-4 py-3">
                        <select name="escalation_to_role" class="border rounded px-2 py-1 text-sm">
                            @foreach(['tl' => 'Team Lead', 'it_head' => 'IT Head', 'admin' => 'Admin'] as $val => $label)
                                <option value="{{ $val }}" @selected($c->escalation_to_role === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button type="submit" class="text-white px-3 py-1 rounded text-xs font-medium" style="background:#0056B3;">Save</button>
                    </td>
                </form>
            </tr>
            @empty
            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No TAT configurations. Run the TAT seeder.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
