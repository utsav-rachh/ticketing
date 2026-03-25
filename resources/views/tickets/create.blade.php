@extends('layouts.app')
@section('title', 'New Ticket')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-bold text-gray-700 mb-6">Raise a New Ticket</h2>
    <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Support Type</label>
            <select name="support_type" id="support_type" required onchange="filterCategories()"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select support type...</option>
                <option value="application" {{ old('support_type') === 'application' ? 'selected' : '' }}>Application Support</option>
                <option value="infrastructure" {{ old('support_type') === 'infrastructure' ? 'selected' : '' }}>IT Infrastructure</option>
                <option value="admin" {{ old('support_type') === 'admin' ? 'selected' : '' }}>Admin / HR Support</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category_id" id="category_id" required onchange="filterSubcategories()"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select category...</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" data-type="{{ $cat->support_type }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Issue Type</label>
            <select name="subcategory_id" id="subcategory_id" required onchange="setDefaultPriority()"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Select issue type...</option>
                @foreach($categories as $cat)
                    @foreach($cat->activeSubcategories as $sub)
                    <option value="{{ $sub->id }}" data-category="{{ $sub->category_id }}" data-priority="{{ $sub->default_priority }}" {{ old('subcategory_id') == $sub->id ? 'selected' : '' }}>
                        {{ $sub->name }}
                    </option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select name="priority" id="priority" required
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="low" {{ old('priority','low') === 'low' ? 'selected' : '' }}>Low (24h TAT)</option>
                <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium (8h TAT)</option>
                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High (4h TAT)</option>
                <option value="critical" {{ old('priority') === 'critical' ? 'selected' : '' }}>Critical (2h TAT)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="500"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Brief description of the issue">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(optional)</span></label>
            <textarea name="description" rows="4"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Provide detailed steps to reproduce, error messages, screenshots...">{{ old('description') }}</textarea>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 text-sm font-medium">Submit Ticket</button>
            <a href="{{ route('tickets.index') }}" class="bg-gray-100 text-gray-700 px-6 py-2 rounded hover:bg-gray-200 text-sm">Cancel</a>
        </div>
    </form>
</div>
<script>
function filterCategories() {
    const type = document.getElementById('support_type').value;
    const catSel = document.getElementById('category_id');
    const subSel = document.getElementById('subcategory_id');
    Array.from(catSel.options).forEach(opt => {
        opt.style.display = (!opt.value || opt.dataset.type === type) ? '' : 'none';
    });
    catSel.value = '';
    subSel.value = '';
}
function filterSubcategories() {
    const catId = document.getElementById('category_id').value;
    const subSel = document.getElementById('subcategory_id');
    Array.from(subSel.options).forEach(opt => {
        opt.style.display = (!opt.value || opt.dataset.category === catId) ? '' : 'none';
    });
    subSel.value = '';
}
function setDefaultPriority() {
    const subSel = document.getElementById('subcategory_id');
    const sel = subSel.options[subSel.selectedIndex];
    if (sel && sel.dataset.priority) {
        document.getElementById('priority').value = sel.dataset.priority;
    }
}
</script>
@endsection
