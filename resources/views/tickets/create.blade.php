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

                @php
                    $authUser     = auth()->user();
                    $defaultBranch = $authUser->branch_id;
                    $defaultRegion = $authUser->region_id ?? optional($branches->firstWhere('id', $defaultBranch))->region_id;
                @endphp

                <div class="space-y-5">

                    {{-- 1. Location: state + branch (auto-selected from the logged-in user, editable) --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Location</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">State <span class="text-red-500">*</span></label>
                                <select name="region_id" id="regionSelect" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                                    <option value="">— Select state —</option>
                                    @foreach($regions as $rg)
                                        <option value="{{ $rg->id }}" {{ old('region_id', $defaultRegion) == $rg->id ? 'selected' : '' }}>{{ $rg->name }}</option>
                                    @endforeach
                                </select>
                                @error('region_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Branch <span class="text-red-500">*</span></label>
                                <select name="branch_id" id="branchSelect" required
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                                    <option value="">— Select branch —</option>
                                    @foreach($branches as $br)
                                        <option value="{{ $br->id }}" data-region="{{ $br->region_id }}"
                                            {{ old('branch_id', $defaultBranch) == $br->id ? 'selected' : '' }}>
                                            {{ $br->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('branch_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 2. Employee details --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Employee details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Employee ID <span class="text-red-500">*</span></label>
                                <input type="text" name="employee_contact_employee_id" required maxlength="50"
                                    value="{{ old('employee_contact_employee_id', $authUser->employee_id) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                @error('employee_contact_employee_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                                <input type="text" name="employee_contact_name" required maxlength="150"
                                    value="{{ old('employee_contact_name', $authUser->name) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                @error('employee_contact_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone <span class="text-red-500">*</span></label>
                                <input type="text" name="employee_contact_phone" required maxlength="20"
                                    value="{{ old('employee_contact_phone', $authUser->phone) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                @error('employee_contact_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-gray-400">(optional)</span></label>
                                <input type="email" name="employee_contact_email" maxlength="150"
                                    value="{{ old('employee_contact_email', $authUser->email) }}"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                                @error('employee_contact_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Custom issue (shown only when subcategory is "Others") --}}
                    <div id="customIssueField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Describe the issue <span class="text-red-500">*</span></label>
                        <textarea name="custom_issue" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                            placeholder="Since this isn't in the list, describe in a line or two...">{{ old('custom_issue') }}</textarea>
                        @error('custom_issue') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- 3. Subject --}}
                    <div>
                        <div class="flex items-end justify-between mb-1.5">
                            <label class="block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
                            <span id="subjectCounter" class="text-xs text-gray-400">0 / 150</span>
                        </div>
                        <input type="text" id="subjectInput" name="subject" value="{{ old('subject') }}" required maxlength="150"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                            placeholder="Brief summary of the issue (max 150 characters)">
                        <p id="subjectError" class="text-xs text-red-600 mt-1 hidden"></p>
                        @error('subject') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- 4. Description --}}
                    <div>
                        <div class="flex items-end justify-between mb-1.5">
                            <label class="block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
                            <span id="descriptionCounter" class="text-xs text-gray-400">0 / 500</span>
                        </div>
                        <textarea id="descriptionInput" name="description" rows="5" required maxlength="500"
                            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400"
                            placeholder="Steps to reproduce, error messages, any additional context (max 500 characters)...">{{ old('description') }}</textarea>
                        <p id="descriptionError" class="text-xs text-red-600 mt-1 hidden"></p>
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Vendor (infrastructure only) --}}
                    <div id="vendorField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor <span class="text-gray-400">(optional)</span></label>
                        <select name="vendor_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                            <option value="">— No vendor —</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}" {{ old('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($canLinkProject)
                    {{-- Project (Admin / IT Head only) --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-blue-800 mb-3">Project</h3>
                        @php
                            $defaultMode = old('project_mode', $preselectedProjectId ? 'existing' : 'none');
                        @endphp
                        <div class="flex flex-wrap gap-4 mb-3 text-sm">
                            @foreach(['none' => 'No project', 'existing' => 'Pick existing', 'new' => '+ New project'] as $val => $lbl)
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="project_mode" value="{{ $val }}" class="project-mode-radio" {{ $defaultMode === $val ? 'checked' : '' }}>
                                <span>{{ $lbl }}</span>
                            </label>
                            @endforeach
                        </div>

                        <div class="project-mode-panel project-mode-existing {{ $defaultMode === 'existing' ? '' : 'hidden' }}">
                            <select name="project_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                                <option value="">— Select a project —</option>
                                @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ old('project_id', $preselectedProjectId) == $p->id ? 'selected' : '' }}>{{ $p->number }} · {{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('project_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="project-mode-panel project-mode-new {{ $defaultMode === 'new' ? '' : 'hidden' }} space-y-3">
                            <input type="text" name="new_project_name" maxlength="200"
                                   value="{{ old('new_project_name') }}"
                                   placeholder="Project name"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">
                            @error('new_project_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            <select name="new_project_owner_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                                <option value="">— Project owner (management user) —</option>
                                @foreach($managementOwners as $o)
                                <option value="{{ $o->id }}" {{ old('new_project_owner_id') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                                @endforeach
                            </select>
                            @error('new_project_owner_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            <textarea name="new_project_description" rows="2" maxlength="5000"
                                      placeholder="Description (optional)"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">{{ old('new_project_description') }}</textarea>

                            <div class="grid grid-cols-2 gap-3">
                                <input type="date" name="new_project_start_date" value="{{ old('new_project_start_date') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" placeholder="Start date">
                                <input type="date" name="new_project_end_date" value="{{ old('new_project_end_date') }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" placeholder="End date">
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- 5. Attachments --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Attachments <span class="text-gray-400">(optional, max 10 MB total)</span></label>
                        <input type="file" id="attachmentsInput" name="attachments[]" multiple
                               class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                        <p id="attachmentsError" class="text-xs text-red-600 mt-1 hidden"></p>
                        @error('attachments') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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

// Live character counters for subject + description, with on-screen error past the limit.
(function () {
    function bindCounter(inputId, counterId, errorId, max, fieldName) {
        const input = document.getElementById(inputId);
        const counter = document.getElementById(counterId);
        const errorEl = document.getElementById(errorId);
        if (!input || !counter) return;

        function update() {
            const len = input.value.length;
            counter.textContent = `${len} / ${max}`;
            const over = len >= max;
            counter.classList.toggle('text-red-600', over);
            counter.classList.toggle('font-semibold', over);
            counter.classList.toggle('text-gray-400', !over);
            if (over) {
                errorEl.textContent = `${fieldName} cannot exceed ${max} characters.`;
                errorEl.classList.remove('hidden');
            } else {
                errorEl.classList.add('hidden');
            }
        }
        input.addEventListener('input', update);
        update();
    }
    bindCounter('subjectInput',     'subjectCounter',     'subjectError',     150, 'Subject');
    bindCounter('descriptionInput', 'descriptionCounter', 'descriptionError', 500, 'Description');
})();

// State <-> branch coupling: filter branches by chosen state, and snap state to a branch's region.
(function () {
    const regionSel = document.getElementById('regionSelect');
    const branchSel = document.getElementById('branchSelect');
    if (!regionSel || !branchSel) return;

    function filterBranches() {
        const rid = regionSel.value;
        let firstVisible = null;
        Array.from(branchSel.options).forEach(opt => {
            if (!opt.value) { opt.hidden = false; return; }
            const match = !rid || opt.dataset.region === rid;
            opt.hidden = !match;
            if (match && firstVisible === null) firstVisible = opt;
        });
        const cur = branchSel.selectedOptions[0];
        if (!cur || cur.hidden) {
            branchSel.value = firstVisible ? firstVisible.value : '';
        }
    }

    regionSel.addEventListener('change', filterBranches);
    branchSel.addEventListener('change', () => {
        const opt = branchSel.selectedOptions[0];
        if (opt && opt.dataset.region && regionSel.value !== opt.dataset.region) {
            regionSel.value = opt.dataset.region;
            filterBranches();
        }
    });
    filterBranches();
})();

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

// Project mode toggle (Admin/IT Head only — radios only render when allowed).
(function () {
    const radios = document.querySelectorAll('.project-mode-radio');
    if (radios.length === 0) return;

    function refresh() {
        const val = document.querySelector('.project-mode-radio:checked')?.value || 'none';
        document.querySelectorAll('.project-mode-panel').forEach(p => p.classList.add('hidden'));
        const panel = document.querySelector('.project-mode-' + val);
        if (panel) panel.classList.remove('hidden');
    }
    radios.forEach(r => r.addEventListener('change', refresh));
    refresh();
})();

// Total-attachment-size pre-check: 10 MB across all selected files (initial create only).
(function () {
    const input = document.getElementById('attachmentsInput');
    const errorEl = document.getElementById('attachmentsError');
    const form = document.getElementById('ticketForm');
    const MAX = 10 * 1024 * 1024;
    if (!input || !errorEl || !form) return;

    function totalSize() {
        let bytes = 0;
        for (const f of input.files) bytes += f.size;
        return bytes;
    }
    function check() {
        const total = totalSize();
        if (total > MAX) {
            const mb = (total / 1048576).toFixed(2);
            errorEl.textContent = `Total attachments exceed 10 MB (current: ${mb} MB). Remove some files before submitting.`;
            errorEl.classList.remove('hidden');
            return false;
        }
        errorEl.classList.add('hidden');
        return true;
    }
    input.addEventListener('change', check);
    form.addEventListener('submit', (e) => {
        if (!check()) e.preventDefault();
    });
})();
</script>
@endsection
