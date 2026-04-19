@extends('layouts.app')
@section('title', 'Manage Categories')
@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-gray-700">Categories</h2>
    <a href="{{ route('admin.categories.create') }}" class="text-white px-4 py-2 rounded text-sm font-medium" style="background:#0056B3;">+ New category</a>
</div>
<div class="grid grid-cols-1 gap-4">
    @foreach($categories->groupBy('support_type') as $type => $cats)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold text-gray-700 mb-4 capitalize">{{ str_replace('_',' ',$type) }} Support</h3>
        <div class="space-y-3">
            @foreach($cats as $cat)
            <details class="border rounded p-3">
                <summary class="cursor-pointer font-medium text-gray-700 flex justify-between">
                    <span>{{ $cat->name }} <span class="text-xs text-gray-400">({{ $cat->subcategories->count() }} issues)</span></span>
                    <span class="flex items-center gap-3">
                        <a href="{{ route('admin.subcategories.index', ['category_id' => $cat->id]) }}" class="text-xs text-brand-600 hover:underline">Manage issues</a>
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="text-xs text-brand-600 hover:underline">Edit</a>
                        <span class="{{ $cat->is_active ? 'text-green-600' : 'text-red-500' }} text-xs">{{ $cat->is_active ? 'Active' : 'Inactive' }}</span>
                    </span>
                </summary>
                <div class="mt-2 pl-3 text-sm text-gray-600 grid grid-cols-3 gap-1">
                    @foreach($cat->subcategories as $sub)
                    <span class="text-gray-500">{{ $sub->name }}</span>
                    @endforeach
                </div>
            </details>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
