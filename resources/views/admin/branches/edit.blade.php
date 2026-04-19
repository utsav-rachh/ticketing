@extends('layouts.app')
@section('title', $branch->exists ? 'Edit Branch' : 'New Branch')
@section('content')
<div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">{{ $branch->exists ? 'Edit Branch' : 'New Branch' }}</h2>
    <form method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}">
        @csrf
        @if($branch->exists) @method('PATCH') @endif
        <div class="space-y-4">
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Region</span>
                <select name="region_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach($regions as $r)
                    <option value="{{ $r->id }}" {{ old('region_id', $branch->region_id) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Name</span>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Code</span>
                <input type="text" name="code" value="{{ old('code', $branch->code) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Address</span>
                <textarea name="address" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('address', $branch->address) }}</textarea>
            </label>
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-600">Active</span>
            </label>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
            <a href="{{ route('admin.branches.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
