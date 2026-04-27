@extends('layouts.app')
@section('title', 'Issue Types (Subcategories)')
@section('content')
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-gray-700">Issue Types</h2>
    <a href="{{ route('admin.subcategories.create', ['category_id' => $categoryId]) }}" class="text-white px-4 py-2 rounded text-sm font-medium" style="background:#0056B3;">+ New issue type</a>
</div>

@if($errors->has('delete'))
<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4 text-sm">{{ $errors->first('delete') }}</div>
@endif

<form method="GET" class="bg-white shadow rounded p-4 flex items-end gap-3 mb-4">
    <label class="block flex-1">
        <span class="text-xs font-medium text-gray-500">Filter by category</span>
        <select name="category_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">— all categories —</option>
            @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ $categoryId == $c->id ? 'selected' : '' }}>{{ $c->support_type }} · {{ $c->name }}</option>
            @endforeach
        </select>
    </label>
</form>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Default priority</th>
                <th class="px-4 py-3 text-left">Order</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($subcategories as $s)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-600">{{ $s->category->name ?? '—' }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $s->name }}</td>
                <td class="px-4 py-3 text-xs uppercase">{{ $s->default_priority }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $s->sort_order }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $s->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $s->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="inline-flex items-center gap-3">
                        <a href="{{ route('admin.subcategories.edit', $s) }}" class="text-brand-600 text-xs hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.subcategories.destroy', $s) }}"
                              onsubmit="return confirm('Delete issue type &quot;{{ $s->name }}&quot;? Existing tickets that reference it will block deletion.')"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 text-xs hover:underline">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No issue types found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-4 border-t">{{ $subcategories->links() }}</div>
</div>
@endsection
