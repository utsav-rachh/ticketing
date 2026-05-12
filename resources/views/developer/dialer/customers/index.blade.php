@extends('layouts.developer')
@section('title', 'Dialer · Customers')
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

<div class="flex items-start justify-between flex-wrap gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-bold text-white">Customers</h1>
        <p class="text-slate-400 text-sm mt-1">{{ $customers->total() }} in the dialer database. Deduplicated on phone number.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('developer.dialer.customers.import') }}" class="text-sm px-3 py-2 rounded bg-slate-700 hover:bg-slate-600 text-white">CSV import</a>
        <button type="button" onclick="document.getElementById('addCustomer').classList.toggle('hidden')" class="text-sm px-3 py-2 rounded bg-emerald-600 hover:bg-emerald-500 text-white">+ Add customer</button>
    </div>
</div>

<div id="addCustomer" class="hidden mb-6 bg-slate-800/70 border border-slate-700 rounded-lg p-5">
    <form method="POST" action="{{ route('developer.dialer.customers.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-xs text-slate-400 mb-1">Name *</label>
            <input name="name" required value="{{ old('name') }}" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Phone *</label>
            <input name="phone" required value="{{ old('phone') }}" placeholder="10-digit" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white placeholder-slate-600">
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Company</label>
            <input name="company" value="{{ old('company') }}" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">
        </div>
        <div>
            <label class="block text-xs text-slate-400 mb-1">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">
        </div>
        <div>
            <button class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded px-3 py-2">Save</button>
        </div>
        <div class="md:col-span-5">
            <label class="block text-xs text-slate-400 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white">{{ old('notes') }}</textarea>
        </div>
    </form>
</div>

<form method="GET" class="mb-4">
    <input name="q" value="{{ $q }}" placeholder="Search name, phone, company, email…"
           class="w-full md:w-96 bg-slate-900 border border-slate-700 rounded px-3 py-2 text-sm text-white placeholder-slate-600">
</form>

<div class="bg-slate-800/70 border border-slate-700 rounded-lg overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-700">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Phone</th>
                <th class="px-4 py-2">Company</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Calls</th>
                <th class="px-4 py-2">Source</th>
                <th class="px-4 py-2">Added</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-700/60">
            @forelse($customers as $c)
            <tr class="hover:bg-slate-700/30">
                <td class="px-4 py-2 text-white">{{ $c->name }}</td>
                <td class="px-4 py-2 text-slate-300">{{ $c->phone }}</td>
                <td class="px-4 py-2 text-slate-300">{{ $c->company ?: '—' }}</td>
                <td class="px-4 py-2 text-slate-300">{{ $c->email ?: '—' }}</td>
                <td class="px-4 py-2 text-slate-400">{{ $c->tickets()->count() }}</td>
                <td class="px-4 py-2 text-slate-400">{{ $c->imported_from ?: '—' }}</td>
                <td class="px-4 py-2 text-slate-400">{{ $c->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No customers{{ $q ? ' match “'.$q.'”' : ' yet' }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
