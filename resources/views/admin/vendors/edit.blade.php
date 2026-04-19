@extends('layouts.app')
@section('title', $vendor->exists ? 'Edit Vendor' : 'New Vendor')
@section('content')
<div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">{{ $vendor->exists ? 'Edit Vendor' : 'New Vendor' }}</h2>
    <form method="POST" action="{{ $vendor->exists ? route('admin.vendors.update', $vendor) : route('admin.vendors.store') }}">
        @csrf
        @if($vendor->exists) @method('PATCH') @endif
        <div class="space-y-4">
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Name</span>
                <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Contact person</span>
                <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs font-medium text-gray-500">Phone</span>
                    <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </label>
                <label class="block">
                    <span class="text-xs font-medium text-gray-500">Email</span>
                    <input type="email" name="email" value="{{ old('email', $vendor->email) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </label>
            </div>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Notes</span>
                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('notes', $vendor->notes) }}</textarea>
            </label>
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vendor->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-600">Active</span>
            </label>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
            <a href="{{ route('admin.vendors.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
