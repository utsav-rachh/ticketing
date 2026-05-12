@extends('layouts.developer')
@section('title', 'Asset Management')
@section('content')

<div class="mb-6 flex items-start justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Asset Management — Prototype</h1>
        <p class="text-gray-500 text-sm mt-1">Working scope: physical + software inventory across all branches, ~700 employees.</p>
    </div>
    <a href="{{ route('developer.home') }}" class="text-xs text-gray-500 hover:text-brand-600">← Back to apps</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['Physical assets', '0', 'Laptops, monitors, mobiles, peripherals'],
        ['Software licences', '0', 'OS, productivity, dev tools, SaaS seats'],
        ['Branches in scope', \App\Models\Branch::count(), 'Linked to existing branch master'],
    ] as [$label, $value, $hint])
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
        <div class="text-xs uppercase tracking-wide text-gray-400">{{ $label }}</div>
        <div class="text-2xl font-extrabold text-gray-800 mt-1">{{ $value }}</div>
        <div class="text-[11px] text-gray-400 mt-1">{{ $hint }}</div>
    </div>
    @endforeach
</div>

<div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 mb-6">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Planned modules</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-gray-600">
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="font-semibold text-gray-800">Physical asset register</div>
            <ul class="mt-2 text-xs text-gray-500 list-disc list-inside space-y-1">
                <li>Asset tag, serial, make/model, condition, value</li>
                <li>Branch + assigned employee + custodian</li>
                <li>Lifecycle: procured → issued → returned → repaired → retired</li>
                <li>Bulk import via CSV; barcode print for tags</li>
            </ul>
        </div>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="font-semibold text-gray-800">Software / licence register</div>
            <ul class="mt-2 text-xs text-gray-500 list-disc list-inside space-y-1">
                <li>Product, version, licence type (perpetual / seats / subscription)</li>
                <li>Seat allocation per user; expiry + renewal alerts</li>
                <li>Vendor + PO + invoice link to existing Vendor master</li>
            </ul>
        </div>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="font-semibold text-gray-800">Audit + reconciliation</div>
            <ul class="mt-2 text-xs text-gray-500 list-disc list-inside space-y-1">
                <li>Periodic branch audit checklist</li>
                <li>HR roster sync — flag orphaned assets after exits</li>
                <li>Read-only export for finance / internal audit</li>
            </ul>
        </div>
        <div class="border border-gray-200 rounded-lg p-4">
            <div class="font-semibold text-gray-800">Ticket bridge</div>
            <ul class="mt-2 text-xs text-gray-500 list-disc list-inside space-y-1">
                <li>Open a ticket from any asset record (auto-fills branch + serial)</li>
                <li>Tickets back-link to the asset; closes with disposition</li>
                <li>Procurement requests flow through expense approval</li>
            </ul>
        </div>
    </div>
</div>

<div class="bg-gray-50 border border-dashed border-gray-300 rounded-lg p-5 text-center text-gray-500 text-sm">
    No data yet — drop in a scope-of-work and I'll wire models, migrations, controllers and views.
</div>

@endsection
