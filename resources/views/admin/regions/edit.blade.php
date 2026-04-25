@extends('layouts.app')
@section('title', $region->exists ? 'Edit State' : 'New State')
@section('content')
<div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">{{ $region->exists ? 'Edit State' : 'New State' }}</h2>
    <form method="POST" action="{{ $region->exists ? route('admin.regions.update', $region) : route('admin.regions.store') }}">
        @csrf
        @if($region->exists) @method('PATCH') @endif
        <div class="space-y-4">
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Name</span>
                <input type="text" name="name" value="{{ old('name', $region->name) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Code (unique, short)</span>
                <input type="text" name="code" value="{{ old('code', $region->code) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono">
            </label>
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $region->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-600">Active</span>
            </label>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
            <a href="{{ route('admin.regions.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
