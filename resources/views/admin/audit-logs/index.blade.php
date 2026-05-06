@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<h2 class="text-lg md:text-xl font-bold text-gray-700 mb-4">Audit Logs</h2>

<form method="GET" class="bg-white shadow rounded p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 mb-4">
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Model</span>
        <select name="auditable_type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— any —</option>
            @foreach($types as $t)
            <option value="{{ $t }}" {{ request('auditable_type') === $t ? 'selected' : '' }}>
                {{ \App\Models\AuditLog::MODEL_LABELS[$t] ?? class_basename($t) }}
            </option>
            @endforeach
        </select>
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Action</span>
        <select name="action" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— any —</option>
            @foreach($actions as $a)
            <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">From</span>
        <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">To</span>
        <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <div class="flex items-end gap-2">
        <button type="submit" class="text-white px-4 py-2 rounded text-sm" style="background:#0056B3;">Filter</button>
        <a href="{{ route('admin.audit-logs.index') }}" class="bg-gray-100 text-gray-600 px-3 py-2 rounded text-sm">Clear</a>
    </div>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm" data-mobile="cards">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left w-40">When</th>
                <th class="px-4 py-3 text-left w-40">User</th>
                <th class="px-4 py-3 text-left w-28">Action</th>
                <th class="px-4 py-3 text-left">Change</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
            <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50/40' }} hover:bg-blue-50/30 align-top">
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ $log->user->name ?? 'System' }}</td>
                <td class="px-4 py-3 text-xs">
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @switch($log->action)
                            @case('created') bg-green-100 text-green-700 @break
                            @case('updated') bg-blue-100 text-blue-700 @break
                            @case('deleted')
                            @case('force_deleted') bg-red-100 text-red-700 @break
                            @default bg-gray-100 text-gray-700
                        @endswitch">
                        {{ $log->action }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-700">
                    <div class="text-gray-800">{{ $log->humanLabel() }}</div>
                    @if($log->changes)
                    <details class="mt-1">
                        <summary class="cursor-pointer text-[11px] text-gray-400 hover:text-gray-600 select-none">show raw</summary>
                        <pre class="whitespace-pre-wrap break-all max-w-2xl text-[11px] text-gray-500 mt-1 bg-gray-50 p-2 rounded border border-gray-100">{{ json_encode($log->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No audit logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-4 md:px-6 py-3 md:py-4 border-t">{{ $logs->links() }}</div>
</div>
@endsection
