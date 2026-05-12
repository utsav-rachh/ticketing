@extends('layouts.developer')
@section('title', 'Dialer · CSV import')
@section('content')

@include('developer.dialer._subnav')

<div class="flex items-center justify-between mb-5">
    <h1 class="text-xl md:text-2xl font-bold text-gray-800">CSV import</h1>
    <a href="{{ route('developer.dialer.customers.index') }}" class="text-xs text-gray-500 hover:text-brand-600">← Customers</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Upload a file</h2>
        <form method="POST" action="{{ route('developer.dialer.customers.import.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="file" name="file" accept=".csv,text/csv" required
                   class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
            <button class="w-full bg-brand-500 hover:bg-brand-600 text-white text-sm font-medium rounded px-3 py-2">Import</button>
        </form>
        <div class="mt-4 text-xs text-gray-500 leading-relaxed">
            <p class="font-semibold text-gray-700 mb-1">Format</p>
            First row = headers. Recognised columns (any order, case-insensitive):
            <code class="text-gray-700 bg-gray-100 px-1 rounded">name</code>, <code class="text-gray-700 bg-gray-100 px-1 rounded">phone</code>, <code class="text-gray-700 bg-gray-100 px-1 rounded">email</code>, <code class="text-gray-700 bg-gray-100 px-1 rounded">company</code>, <code class="text-gray-700 bg-gray-100 px-1 rounded">notes</code>.
            <code class="text-gray-700 bg-gray-100 px-1 rounded">phone</code> is required. Rows whose phone already exists are skipped (deduplication on the normalised number).
        </div>
    </div>

    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg shadow-sm overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200 text-sm font-semibold text-gray-800 flex items-center justify-between">
            <span>Recent imports</span>
            <a href="{{ route('developer.dialer.customers.import') }}" class="text-xs text-gray-500 hover:text-brand-600">Refresh</a>
        </div>
        <table class="w-full text-sm">
            <thead class="text-left text-xs uppercase tracking-wide text-gray-400 border-b border-gray-200 bg-gray-50">
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
            <tbody class="divide-y divide-gray-100">
                @forelse($imports as $imp)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-medium text-gray-800">{{ $imp->filename }}</td>
                    <td class="px-4 py-2">
                        @php $c = ['processing' => 'text-blue-600', 'completed' => 'text-green-600', 'failed' => 'text-red-600'][$imp->status] ?? 'text-gray-600'; @endphp
                        <span class="capitalize {{ $c }}">{{ $imp->status }}</span>
                        @if($imp->error)<div class="text-[11px] text-red-500 mt-0.5">{{ $imp->error }}</div>@endif
                    </td>
                    <td class="px-4 py-2 text-gray-600">{{ $imp->total_rows }}</td>
                    <td class="px-4 py-2 text-green-600">{{ $imp->imported }}</td>
                    <td class="px-4 py-2 text-amber-600">{{ $imp->duplicates }}</td>
                    <td class="px-4 py-2 text-red-600">{{ $imp->failed }}</td>
                    <td class="px-4 py-2 text-gray-400">{{ $imp->importer?->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-400">{{ $imp->created_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No imports yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
