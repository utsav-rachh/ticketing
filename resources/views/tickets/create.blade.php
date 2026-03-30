@extends('layouts.app')
@section('title', 'New Ticket')
@section('content')
<div class="max-w-3xl mx-auto">

    <!-- Progress Steps -->
    <div class="flex items-center justify-between mb-8 px-4">
        <template x-if="true">
            <div x-data class="flex items-center justify-between w-full" id="progress-steps">
            </div>
        </template>
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

    <form method="POST" action="{{ route('tickets.store') }}" id="ticketForm">
        @csrf

        <!-- Step 1: Support Type -->
        <div id="step1" class="step-panel">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <h2 class="text-lg font-bold text-gray-800 mb-1">What do you need help with?</h2>
                <p class="text-sm text-gray-500 mb-6">Choose the type of support you require</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="support-card group cursor-pointer" data-type="application">
                        <input type="radio" name="support_type" value="application" class="hidden" {{ old('support_type') === 'application' ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl p-6 text-center transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md">
                            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center transition-colors duration-200" style="background: #E8F1F8;">
                                <svg class="w-7 h-7" style="color: #0056B3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-1">Application Support</h3>
                            <p class="text-xs text-gray-500">Software, apps & system issues</p>
                        </div>
                    </label>
                    <label class="support-card group cursor-pointer" data-type="infrastructure">
                        <input type="radio" name="support_type" value="infrastructure" class="hidden" {{ old('support_type') === 'infrastructure' ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl p-6 text-center transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md">
                            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center transition-colors duration-200" style="background: #E8F1F8;">
                                <svg class="w-7 h-7" style="color: #0056B3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-1">IT Infrastructure</h3>
                            <p class="text-xs text-gray-500">Hardware, network & connectivity</p>
                        </div>
                    </label>
                    <label class="support-card group cursor-pointer" data-type="admin">
                        <input type="radio" name="support_type" value="admin" class="hidden" {{ old('support_type') === 'admin' ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl p-6 text-center transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md">
                            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center transition-colors duration-200" style="background: #E8F1F8;">
                                <svg class="w-7 h-7" style="color: #0056B3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-1">Admin / HR</h3>
                            <p class="text-xs text-gray-500">Administrative & HR requests</p>
                        </div>
                    </label>
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
                    <button type="button" onclick="goToStep(1)" class="text-sm text-brand-500 hover:text-brand-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                </div>
                <div id="categoryCards" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($categories as $cat)
                    <label class="category-card group cursor-pointer" data-type="{{ $cat->support_type }}">
                        <input type="radio" name="category_id" value="{{ $cat->id }}" class="hidden" {{ old('category_id') == $cat->id ? 'checked' : '' }}>
                        <div class="border-2 border-gray-200 rounded-xl px-5 py-4 transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: #E8F1F8;">
                                <svg class="w-5 h-5" style="color: #0056B3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 text-sm">{{ $cat->name }}</h3>
                                @if($cat->description)
                                <p class="text-xs text-gray-500 mt-0.5">{{ $cat->description }}</p>
                                @endif
                            </div>
                            <svg class="w-5 h-5 text-brand-500 ml-auto opacity-0 group-has-[:checked]:opacity-100 transition-opacity flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Step 3: Issue Type + Priority -->
        <div id="step3" class="step-panel hidden">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 mb-1">What's the issue?</h2>
                        <p class="text-sm text-gray-500">Select the specific issue type</p>
                    </div>
                    <button type="button" onclick="goToStep(2)" class="text-sm text-brand-500 hover:text-brand-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                </div>
                <div id="subcategoryCards" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-8">
                    @foreach($categories as $cat)
                        @foreach($cat->activeSubcategories as $sub)
                        <label class="subcategory-card group cursor-pointer" data-category="{{ $sub->category_id }}" data-priority="{{ $sub->default_priority }}">
                            <input type="radio" name="subcategory_id" value="{{ $sub->id }}" class="hidden" {{ old('subcategory_id') == $sub->id ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-xl px-5 py-4 transition-all duration-200 hover:border-brand-400 hover:shadow-md group-has-[:checked]:border-brand-500 group-has-[:checked]:bg-brand-50 group-has-[:checked]:shadow-md flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background: #E8F1F8;">
                                    <svg class="w-5 h-5" style="color: #0056B3;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
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
                                <svg class="w-5 h-5 text-brand-500 ml-auto opacity-0 group-has-[:checked]:opacity-100 transition-opacity flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </div>
                        </label>
                        @endforeach
                    @endforeach
                </div>

                <!-- Priority -->
                <div>
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Priority Level</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="group cursor-pointer">
                            <input type="radio" name="priority" value="low" class="hidden" {{ old('priority','low') === 'low' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-xl py-3 px-4 text-center transition-all duration-200 hover:shadow-md group-has-[:checked]:border-green-500 group-has-[:checked]:bg-green-50 group-has-[:checked]:shadow-md">
                                <div class="w-3 h-3 rounded-full bg-green-500 mx-auto mb-2"></div>
                                <div class="text-sm font-semibold text-gray-800">Low</div>
                                <div class="text-[11px] text-gray-500">24h TAT</div>
                            </div>
                        </label>
                        <label class="group cursor-pointer">
                            <input type="radio" name="priority" value="medium" class="hidden" {{ old('priority') === 'medium' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-xl py-3 px-4 text-center transition-all duration-200 hover:shadow-md group-has-[:checked]:border-yellow-500 group-has-[:checked]:bg-yellow-50 group-has-[:checked]:shadow-md">
                                <div class="w-3 h-3 rounded-full bg-yellow-500 mx-auto mb-2"></div>
                                <div class="text-sm font-semibold text-gray-800">Medium</div>
                                <div class="text-[11px] text-gray-500">8h TAT</div>
                            </div>
                        </label>
                        <label class="group cursor-pointer">
                            <input type="radio" name="priority" value="high" class="hidden" {{ old('priority') === 'high' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-xl py-3 px-4 text-center transition-all duration-200 hover:shadow-md group-has-[:checked]:border-orange-500 group-has-[:checked]:bg-orange-50 group-has-[:checked]:shadow-md">
                                <div class="w-3 h-3 rounded-full bg-orange-500 mx-auto mb-2"></div>
                                <div class="text-sm font-semibold text-gray-800">High</div>
                                <div class="text-[11px] text-gray-500">4h TAT</div>
                            </div>
                        </label>
                        <label class="group cursor-pointer">
                            <input type="radio" name="priority" value="critical" class="hidden" {{ old('priority') === 'critical' ? 'checked' : '' }}>
                            <div class="border-2 border-gray-200 rounded-xl py-3 px-4 text-center transition-all duration-200 hover:shadow-md group-has-[:checked]:border-red-500 group-has-[:checked]:bg-red-50 group-has-[:checked]:shadow-md">
                                <div class="w-3 h-3 rounded-full bg-red-500 mx-auto mb-2"></div>
                                <div class="text-sm font-semibold text-gray-800">Critical</div>
                                <div class="text-[11px] text-gray-500">2h TAT</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="goToStep(4)" id="step3Next" class="text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition-all hover:shadow-lg" style="background: #0056B3;">
                        Continue
                        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
                    <button type="button" onclick="goToStep(3)" class="text-sm text-brand-500 hover:text-brand-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back
                    </button>
                </div>

                <!-- Summary chips -->
                <div id="selectionSummary" class="flex flex-wrap gap-2 mb-6">
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="500"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-colors"
                            placeholder="Brief description of the issue">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description <span class="text-gray-400">(optional)</span></label>
                        <textarea name="description" rows="5"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-colors"
                            placeholder="Steps to reproduce, error messages, any additional context...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 mt-8 pt-6 border-t border-gray-100">
                    <button type="submit" class="text-white px-8 py-3 rounded-lg text-sm font-semibold transition-all hover:shadow-lg hover:opacity-90" style="background: #0056B3;">
                        Submit Ticket
                    </button>
                    <a href="{{ route('tickets.index') }}" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">Cancel</a>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
const categories = @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'support_type' => $c->support_type]));
let currentStep = 1;

// Auto-advance on card selection
document.querySelectorAll('input[name="support_type"]').forEach(r => {
    r.addEventListener('change', () => setTimeout(() => goToStep(2), 250));
});
document.querySelectorAll('input[name="category_id"]').forEach(r => {
    r.addEventListener('change', () => setTimeout(() => goToStep(3), 250));
});
document.querySelectorAll('input[name="subcategory_id"]').forEach(r => {
    r.addEventListener('change', () => {
        const card = r.closest('.subcategory-card');
        const priority = card.dataset.priority;
        if (priority) {
            const pInput = document.querySelector(`input[name="priority"][value="${priority}"]`);
            if (pInput) pInput.checked = true;
        }
    });
});

function goToStep(step) {
    // Validate before advancing
    if (step > 1 && !document.querySelector('input[name="support_type"]:checked')) return;
    if (step > 2 && !document.querySelector('input[name="category_id"]:checked')) return;
    if (step > 3 && !document.querySelector('input[name="subcategory_id"]:checked')) return;

    // Filter visible cards
    if (step === 2) filterCategories();
    if (step === 3) filterSubcategories();
    if (step === 4) buildSummary();

    // Switch panels
    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('step' + step).classList.remove('hidden');

    // Update progress indicators
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
    // Uncheck if previously selected category doesn't match
    const checked = document.querySelector('input[name="category_id"]:checked');
    if (checked) {
        const card = checked.closest('.category-card');
        if (card.dataset.type !== type) checked.checked = false;
    }
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
    const subLabel = document.querySelector('input[name="subcategory_id"]:checked')?.closest('.subcategory-card')?.querySelector('h3')?.textContent;
    const priority = document.querySelector('input[name="priority"]:checked')?.value;

    const typeNames = { application: 'Application Support', infrastructure: 'IT Infrastructure', admin: 'Admin / HR' };
    const priorityColors = { low: 'bg-green-100 text-green-700', medium: 'bg-yellow-100 text-yellow-700', high: 'bg-orange-100 text-orange-700', critical: 'bg-red-100 text-red-700' };

    container.innerHTML = '';
    if (typeVal) container.innerHTML += `<span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-brand-50 text-brand-700">${typeNames[typeVal]}</span>`;
    if (catLabel) container.innerHTML += `<span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-gray-100 text-gray-700">${catLabel.trim()}</span>`;
    if (subLabel) container.innerHTML += `<span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full bg-gray-100 text-gray-700">${subLabel.trim()}</span>`;
    if (priority) container.innerHTML += `<span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-full ${priorityColors[priority]}">${priority.charAt(0).toUpperCase() + priority.slice(1)} Priority</span>`;
}

// Handle old() values on page load (validation errors)
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
