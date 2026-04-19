@extends('layouts.app')
@section('title', $subcategory->exists ? 'Edit Issue Type' : 'New Issue Type')
@section('content')
<div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">{{ $subcategory->exists ? 'Edit Issue Type' : 'New Issue Type' }}</h2>
    <form method="POST" action="{{ $subcategory->exists ? route('admin.subcategories.update', $subcategory) : route('admin.subcategories.store') }}">
        @csrf
        @if($subcategory->exists) @method('PATCH') @endif
        <div class="space-y-4">
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Category</span>
                <select name="category_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    <option value="">—</option>
                    @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ old('category_id', $subcategory->category_id) == $c->id ? 'selected' : '' }}>
                        {{ $c->support_type }} · {{ $c->name }}
                    </option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Name</span>
                <input type="text" name="name" value="{{ old('name', $subcategory->name) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            </label>
            <label class="block">
                <span class="text-xs font-medium text-gray-500">Description</span>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('description', $subcategory->description) }}</textarea>
            </label>
            <div class="grid grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs font-medium text-gray-500">Default priority</span>
                    <select name="default_priority" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                        @foreach(['critical','high','medium','low'] as $p)
                        <option value="{{ $p }}" {{ old('default_priority', $subcategory->default_priority) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-medium text-gray-500">Sort order</span>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $subcategory->sort_order ?: 10) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </label>
            </div>
            <label class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subcategory->is_active ?? true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-600">Active</span>
            </label>
        </div>
        <div class="mt-6 flex gap-2">
            <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
            <a href="{{ route('admin.subcategories.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection
