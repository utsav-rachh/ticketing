@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<h2 class="text-xl font-bold text-gray-700 mb-4">Audit Logs</h2>

<form method="GET" class="bg-white shadow rounded p-4 grid grid-cols-1 md:grid-cols-5 gap-3 mb-4">
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Model</span>
        <select name="auditable_type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— any —</option>
            @foreach($types as $t)
            <option value="{{ $t }}" {{ request('auditable_type') === $t ? 'selected' : '' }}>{{ class_basename($t) }}</option>
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
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">When</th>
                <th class="px-4 py-3 text-left">User</th>
                <th class="px-4 py-3 text-left">Model</th>
                <th class="px-4 py-3 text-left">Action</th>
                <th class="px-4 py-3 text-left">Changes</th>
                <th class="px-4 py-3 text-left">IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50 align-top">
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                <td class="px-4 py-3 text-xs text-gray-700">{{ $log->user->name ?? 'System' }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ class_basename($log->auditable_type) }}#{{ $log->auditable_id }}</td>
                <td class="px-4 py-3 text-xs"><span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ $log->action }}</span></td>
                <td class="px-4 py-3 text-xs text-gray-600"><pre class="whitespace-pre-wrap break-all max-w-md text-[11px]">{{ json_encode($log->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $log->ip_address }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No audit logs found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $logs->links() }}</div>
</div>
@endsection
