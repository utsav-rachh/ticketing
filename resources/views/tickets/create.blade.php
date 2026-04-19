@extends('layouts.app')
@section('title', 'New Ticket')
@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Progress Steps -->
    <div class="flex items-center justify-between mb-8 px-4">
        <div class="flex items-center w-full" id="stepIndicator">
            <div class="flex flex-col items-center flex-1">
                <div id="stepDot1" class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-brand-500 text-white shadow-md">1</div>
                <span class="text-xs mt-1.5 font-medium text-brand-600">Support Type</span>
            </div>
            <div id="stepLine1" class="flex-1 h-0.5 bg-gray-200 -mt-4 mx-1 transition-all duration-500"></div>
            <div class="flex flex-col items-center flex-1">
                <div id="stepDot2" class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-gray-200 text-gray-400">2</div>
                <span class="text-xs mt-1.5 font-medium text-gray-400" id="stepLabel2">Category</span>
            </div>
            <div id="stepLine2" class="flex-1 h-0.5 bg-gray-200 -mt-4 mx-1 transition-all duration-500"></div>
            <div class="flex flex-col items-center flex-1">
                <div id="stepDot3" class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-gray-200 text-gray-400">3</div>
                <span class="text-xs mt-1.5 font-medium text-gray-400" id="stepLabel3">Issue Type</span>
            </div>
            <div id="stepLine3" class="flex-1 h-0.5 bg-gray-200 -mt-4 mx-1 transition-all duration-500"></div>
            <div class="flex flex-col items-center flex-1">
                <div id="stepDot4" class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-gray-200 text-gray-400">4</div>
                <span class="text-xs mt-1.5 font-medium text-gray-400" id="stepLabel4">Details</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('tickets.store') }}" id="ticketForm" enctype="multipart/form-data">
        @csrf

        <!-- Step 1: Support Type -->
        <div id="step1" class="step-panel">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-lg font-bold text-gray-800 mb-1">What do you need help with?</h2>
                <p class="text-sm text-gray-500 mb-6">Choose the type of support you require</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach([['application','Application Support','Software, apps & system issues'],['infrastructure','IT Infrastructure','Hardware, network & connectivity'],['admin','Admin / HR','Administrative & HR requests']] as [$val,$label,$desc])
                    <label class="support-card group cursor-pointer" data-type="{{ $val }}">
                        <input type="radio" name="support_type" value="{{ $val }}" class="hidden" {{ old('support_type') === $val ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl p-6 text-center transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md">
                            <h3 class="font-semibold text-gray-800 mb-1">{{ $label }}</h3>
                            <p class="text-xs text-gray-500">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Step 2: Category -->
        <div id="step2" class="step-panel hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 mb-1">Select a category</h2>
                        <p class="text-sm text-gray-500">Narrow down the area of your issue</p>
                    </div>
                    <button type="button" onclick="goToStep(1)" class="text-sm text-brand-500 hover:text-brand-700 font-medium">Back</button>
                </div>
                <div id="categoryCards" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($categories as $cat)
                    <label class="category-card group cursor-pointer" data-type="{{ $cat->support_type }}">
                        <input type="radio" name="category_id" value="{{ $cat->id }}" class="hidden" {{ old('category_id') == $cat->id ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl px-5 py-4 transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md">
                            <h3 class="font-semibold text-gray-800 text-sm">{{ $cat->name }}</h3>
                            @if($cat->description)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $cat->description }}</p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Step 3: Issue Type (priority is derived; no user selector) -->
        <div id="step3" class="step-panel hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 mb-1">What's the issue?</h2>
                        <p class="text-sm text-gray-500">Select the closest match — priority is auto-set based on the issue type.</p>
                    </div>
                    <button type="button" onclick="goToStep(2)" class="text-sm text-brand-500 hover:text-brand-700 font-medium">Back</button>
                </div>
                <div id="subcategoryCards" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
                    @foreach($categories as $cat)
                        @foreach($cat->activeSubcategories as $sub)
                        <label class="subcategory-card group cursor-pointer"
                               data-category="{{ $sub->category_id }}"
                               data-priority="{{ $sub->default_priority }}"
                               data-name="{{ $sub->name }}">
                            <input type="radio" name="subcategory_id" value="{{ $sub->id }}" class="hidden" {{ old('subcategory_id') == $sub->id ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-xl px-5 py-4 transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md">
                                <h3 class="font-semibold text-gray-800 text-sm">{{ $sub->name }}</h3>
                                @if($sub->default_priority)
                                <span class="inline-block mt-1 text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full
                                    {{ $sub->default_priority === 'critical' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $sub->default_priority === 'high' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $sub->default_priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $sub->default_priority === 'low' ? 'bg-green-100 text-green-700' : '' }}
                                ">{{ $sub->default_priority }} priority</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    @endforeach
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="goToStep(4)" id="step3Next" class="text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-all hover:shadow-lg" style="background: #0056B3;">
                        Continue
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 4: Details -->
        <div id="step4" class="step-panel hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 mb-1">Describe the issue</h2>
                        <p class="text-sm text-gray-500">Provide details so we can help you faster</p>
                    </div>
                    <button type="button" onclick="goToStep(3)" class="text-sm text-brand-500 hover:text-brand-700 font-medium">Back</button>
                </div>

                <div id="selectionSummary" class="flex flex-wrap gap-2 mb-6"></div>

                <div class="space-y-5">

                    <!-- Custom issue (shown only when subcategory is "Others") -->
                    <div id="customIssueField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Describe the issue <span class="text-red-500">*</span></label>
                        <textarea name="custom_issue" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                            placeholder="Since this isn't in the list, describe in a line or two...">{{ old('custom_issue') }}</textarea>
                        @error('custom_issue') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject <span class="text-red-500">*</span></label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="500"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                            placeholder="Brief summary of the issue">
                        @error('subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-gray-400">(optional)</span></label>
                        <textarea name="description" rows="5"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                            placeholder="Steps to reproduce, error messages, any additional context...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Branch -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Branch</label>
                        <select name="branch_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <option value="">— Select branch —</option>
                            @foreach($branches as $br)
                                <option value="{{ $br->id }}"
                                    {{ (old('branch_id', auth()->user()->branch_id) == $br->id) ? 'selected' : '' }}>
                                    {{ $br->name }} ({{ $br->region->name ?? '—' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vendor (infrastructure only) -->
                    <div id="vendorField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor <span class="text-gray-400">(optional)</span></label>
                        <select name="vendor_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <option value="">— No vendor —</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Raising on behalf (optional contact override) -->
                    <details class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <summary class="cursor-pointer text-sm font-medium text-gray-700">Contact details (optional — override if raising on behalf)</summary>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                            <input type="text" name="employee_contact_name"  value="{{ old('employee_contact_name', auth()->user()->name) }}"  placeholder="Name"  class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <input type="text" name="employee_contact_phone" value="{{ old('employee_contact_phone', auth()->user()->phone) }}" placeholder="Phone" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <input type="email" name="employee_contact_email" value="{{ old('employee_contact_email', auth()->user()->email) }}" placeholder="Email" class="md:col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </details>

                    <!-- Attachments -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Attachments <span class="text-gray-400">(optional, max 10MB each)</span></label>
                        <input type="file" name="attachments[]" multiple
                               class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-8 pt-6 border-t border-gray-100">
                    <button type="submit" class="text-white px-8 py-3 rounded-lg text-sm font-semibold transition-all hover:shadow-lg" style="background: #0056B3;">
                        Submit Ticket
                    </button>
                    <a href="{{ route('tickets.index') }}" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-lg hover:bg-gray-200 text-sm font-medium">Cancel</a>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
let currentStep = 1;

document.querySelectorAll('input[name="support_type"]').forEach(r => {
    r.addEventListener('change', () => setTimeout(() => goToStep(2), 200));
});
document.querySelectorAll('input[name="category_id"]').forEach(r => {
    r.addEventListener('change', () => setTimeout(() => goToStep(3), 200));
});
document.querySelectorAll('input[name="subcategory_id"]').forEach(r => {
    r.addEventListener('change', () => { /* nothing — step 4 is manual */ });
});

function goToStep(step) {
    if (step > 1 && !document.querySelector('input[name="support_type"]:checked')) return;
    if (step > 2 && !document.querySelector('input[name="category_id"]:checked')) return;
    if (step > 3 && !document.querySelector('input[name="subcategory_id"]:checked')) return;

    if (step === 2) filterCategories();
    if (step === 3) filterSubcategories();
    if (step === 4) buildSummary();

    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('step' + step).classList.remove('hidden');

    for (let i = 1; i <= 4; i++) {
        const dot = document.getElementById('stepDot' + i);
        const label = document.getElementById('stepLabel' + i);
        const line = document.getElementById('stepLine' + (i - 1));

        if (i < step) {
            dot.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-green-500 text-white shadow-md';
            dot.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
            if (label) label.className = 'text-xs mt-1.5 font-medium text-green-600';
        } else if (i === step) {
            dot.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-brand-500 text-white shadow-md';
            dot.textContent = i;
            if (label) label.className = 'text-xs mt-1.5 font-medium text-brand-600';
        } else {
            dot.className = 'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 bg-gray-200 text-gray-400';
            dot.textContent = i;
            if (label) label.className = 'text-xs mt-1.5 font-medium text-gray-400';
        }

        if (line) {
            line.className = i <= step
                ? 'flex-1 h-0.5 -mt-4 mx-1 transition-all duration-500 bg-green-500'
                : 'flex-1 h-0.5 -mt-4 mx-1 transition-all duration-500 bg-gray-200';
        }
    }
    currentStep = step;
}

function filterCategories() {
    const type = document.querySelector('input[name="support_type"]:checked')?.value;
    document.querySelectorAll('.category-card').forEach(card => {
        card.style.display = card.dataset.type === type ? '' : 'none';
    });
    const checked = document.querySelector('input[name="category_id"]:checked');
    if (checked) {
        const card = checked.closest('.category-card');
        if (card.dataset.type !== type) checked.checked = false;
    }
    document.getElementById('vendorField').classList.toggle('hidden', type !== 'infrastructure');
}

function filterSubcategories() {
    const catId = document.querySelector('input[name="category_id"]:checked')?.value;
    document.querySelectorAll('.subcategory-card').forEach(card => {
        card.style.display = card.dataset.category === catId ? '' : 'none';
    });
    const checked = document.querySelector('input[name="subcategory_id"]:checked');
    if (checked) {
        const card = checked.closest('.subcategory-card');
        if (card.dataset.category !== catId) checked.checked = false;
    }
}

function buildSummary() {
    const container = document.getElementById('selectionSummary');
    const typeVal = document.querySelector('input[name="support_type"]:checked')?.value;
    const catLabel = document.querySelector('input[name="category_id"]:checked')?.closest('.category-card')?.querySelector('h3')?.textContent;
    const subCard = document.querySelector('input[name="subcategory_id"]:checked')?.closest('.subcategory-card');
    const subLabel = subCard?.dataset.name;
    const priority = subCard?.dataset.priority;

    const typeNames = { application: 'Application Support', infrastructure: 'IT Infrastructure', admin: 'Admin / HR' };
    const priorityColors = { low: 'bg-green-100 text-green-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' };

    container.innerHTML = '';
    if (typeVal) container.innerHTML += `<span class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full bg-brand-50 text-brand-700">${typeNames[typeVal]}</span>`;
    if (catLabel) container.innerHTML += `<span class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full bg-gray-100 text-gray-700">${catLabel.trim()}</span>`;
    if (subLabel) container.innerHTML += `<span class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full bg-gray-100 text-gray-700">${subLabel.trim()}</span>`;
    if (priority) container.innerHTML += `<span class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full ${priorityColors[priority]}">${priority.charAt(0).toUpperCase() + priority.slice(1)} Priority (auto)</span>`;

    // Toggle "Others" free-text field
    const isOthers = subLabel && subLabel.trim().toLowerCase() === 'others';
    document.getElementById('customIssueField').classList.toggle('hidden', !isOthers);
    document.querySelector('textarea[name="custom_issue"]').toggleAttribute('required', isOthers);
}

document.addEventListener('DOMContentLoaded', () => {
    @if(old('support_type'))
        @if(old('subcategory_id'))
            goToStep(4);
        @elseif(old('category_id'))
            goToStep(3);
        @else
            goToStep(2);
        @endif
    @endif
});
</script>
@endsection
