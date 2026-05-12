@extends('layouts.developer')
@section('title', 'Dialer · CSV import')
@section('content')

@include('developer.dialer._subnav')

@if(session('error'))
<div class="mb-4 px-4 py-2 rounded bg-red-500/10 border border-red-500/30 text-red-300 text-sm">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-2 rounded bg-red-500/10 border border-red-500/30 text-red-300 text-sm">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold text-white">CSV import</h1>
    <a href="{{ route('developer.dialer.customers.index') }}" class="text-xs text-slate-400 hover:text-white">← Customers</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-slate-800/70 border border-slate-700 rounded-lg p-5">
        <h2 class="text-sm font-semibold text-white mb-3">Upload a file</h2>
        <form method="POST" action="{{ route('developer.dialer.customers.import.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".csv,text/csv" required
                   class="block w-full text-sm text-slate-300 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:bg-slate-700 file:text-white hover:file:bg-slate-600">
            <button class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded px-3 py-2">Import</button>
        </form>
        <div class="mt-4 text-xs text-slate-400 leading-relaxed">
            <p class="font-semibold text-slate-300 mb-1">Format</p>
            First row = headers. Recognised columns (any order, case-insensitive):
            <code class="text-slate-300">name</code>, <code class="text-slate-300">phone</code>, <code class="text-slate-300">email</code>, <code class="text-slate-300">company</code>, <code class="text-slate-300">notes</code>.
            <code class="text-slate-300">phone</code> is required. Rows whose phone already exists are skipped (deduplication on the normalised number).
        </div>
    </div>

    <div class="lg:col-span-2 bg-slate-800/70 border border-slate-700 rounded-lg overflow-x-auto">
        <div class="px-4 py-3 border-b border-slate-700 text-sm font-semibold text-white flex items-center justify-between">
            <span>Recent imports</span>
            <a href="{{ route('developer.dialer.customers.import') }}" class="text-xs text-slate-400 hover:text-white">Refresh</a>
        </div>
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-700">
                <tr>
                    <th class="px-4 py-2">File</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Rows</th>
                    <th class="px-4 py-2">Imported</th>
                    <th class="px-4 py-2">Duplicates</th>
                    <th class="px-4 py-2">Failed</th>
                    <th class="px-4 py-2">By</th>
                    <th class="px-4 py-2">When</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($imports as $imp)
                <tr class="hover:bg-slate-700/30">
                    <td class="px-4 py-2 text-white">{{ $imp->filename }}</td>
                    <td class="px-4 py-2">
                        @php $c = ['processing' => 'text-sky-300', 'completed' => 'text-emerald-300', 'failed' => 'text-red-300'][$imp->status] ?? 'text-slate-300'; @endphp
                        <span class="capitalize {{ $c }}">{{ $imp->status }}</span>
                        @if($imp->error)<div class="text-[11px] text-red-400/80 mt-0.5">{{ $imp->error }}</div>@endif
                    </td>
                    <td class="px-4 py-2 text-slate-300">{{ $imp->total_rows }}</td>
                    <td class="px-4 py-2 text-emerald-300">{{ $imp->imported }}</td>
                    <td class="px-4 py-2 text-amber-300">{{ $imp->duplicates }}</td>
                    <td class="px-4 py-2 text-red-300">{{ $imp->failed }}</td>
                    <td class="px-4 py-2 text-slate-400">{{ $imp->importer?->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-slate-400">{{ $imp->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">No imports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
