@extends('layouts.app')
@section('title', $vendor->exists ? 'Edit Vendor' : 'New Vendor')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">{{ $vendor->exists ? 'Edit Vendor' : 'New Vendor' }}</h2>
    <form method="POST" action="{{ $vendor->exists ? route('admin.vendors.update', $vendor) : route('admin.vendors.store') }}" enctype="multipart/form-data">
        @csrf
        @if($vendor->exists) @method('PATCH') @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Vendor code</span>
                <input type="text" name="vendor_code" value="{{ old('vendor_code', $vendor->vendor_code) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Vendor name (company)</span>
                <input type="text" name="name" value="{{ old('name', $vendor->name) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Contact person</span>
                <input type="text" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Phone</span>
                <input type="text" name="phone" value="{{ old('phone', $vendor->phone) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block md:col-span-2">
                <span class="text-xs font-medium text-gray-500">Email</span>
                <input type="email" name="email" value="{{ old('email', $vendor->email) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block md:col-span-2">
                <span class="text-xs font-medium text-gray-500">Address</span>
                <textarea name="address" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('address', $vendor->address) }}</textarea>
            </label>
            <label class="block md:col-span-2">
                <span class="text-xs font-medium text-gray-500">Notes</span>
                <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('notes', $vendor->notes) }}</textarea>
            </label>
            <label class="flex items-center gap-2 md:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $vendor->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-600">Active</span>
            </label>
        </div>

        <div class="mt-6 border-t pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Attachments</h3>
            <input type="file" name="attachments[]" multiple class="text-sm w-full">
            <label class="block mt-2">
                <span class="text-xs text-gray-500">Comment (applied to all files in this upload)</span>
                <input type="text" name="attachment_comment" placeholder="e.g. Master service agreement, Facility document, …" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <p class="text-xs text-gray-500 mt-2">
                Add agreement documents, facility, etc. Up to 15&nbsp;MB per file; multiple files allowed.
            </p>
        </div>

        <div class="mt-6 flex gap-2">
            <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
            <a href="{{ route('admin.vendors.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
        </div>
    </form>

    @if($vendor->exists && $vendor->attachments->isNotEmpty())
    <div class="mt-6 border-t pt-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Existing documents</h3>
        <ul class="divide-y border rounded">
            @foreach($vendor->attachments as $att)
            <li class="flex items-center justify-between px-3 py-2 text-sm">
                <div class="min-w-0 flex-1">
                    <a href="{{ asset('storage/'.$att->file_path) }}" target="_blank" class="text-brand-600 hover:underline truncate block">
                        {{ $att->file_name }}
                    </a>
                    <div class="text-xs text-gray-500">
                        {{ number_format(($att->file_size ?? 0)/1024, 0) }} KB
                        &middot; {{ optional($att->created_at)->format('d M Y, H:i') ?? '—' }}
                        @if($att->uploader) &middot; {{ $att->uploader->name }} @endif
                        @if($att->comment) &middot; <em class="text-gray-600">{{ $att->comment }}</em> @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.vendors.attachments.destroy', [$vendor, $att]) }}" onsubmit="return confirm('Remove this attachment?')" class="ml-3">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-600 hover:underline">Delete</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($vendor->exists)
    <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}" class="mt-6 border-t pt-4"
          onsubmit="return confirm('Delete vendor &quot;{{ $vendor->name }}&quot;? Past tickets keep this vendor reference; new ones can no longer be raised against it.');">
        @csrf @method('DELETE')
        <button class="text-red-600 text-sm hover:underline">Delete vendor</button>
    </form>
    @endif
</div>
@endsection
