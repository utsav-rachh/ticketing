@csrf
@if(($user ?? null)?->exists) @method('PATCH') @endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Name</span>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Email</span>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Password {{ ($user ?? null)?->exists ? '(leave blank to keep)' : '' }}</span>
        <input type="password" name="password" {{ ($user ?? null)?->exists ? '' : 'required' }} class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Phone</span>
        <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Employee ID</span>
        <input type="text" name="employee_id" value="{{ old('employee_id', $user->employee_id ?? '') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Department</span>
        <input type="text" name="department" value="{{ old('department', $user->department ?? '') }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Role</span>
        <select name="role" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" required>
            @foreach(['employee','resolver','admin','management'] as $r)
            <option value="{{ $r }}" {{ old('role', $user->role ?? '') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <p class="text-[11px] text-gray-500 mt-1">Management users' tickets are auto red-flagged and critical.</p>
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Resolver Level (resolver only)</span>
        <select name="resolver_level" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">—</option>
            @foreach(['junior' => 'Junior','tl' => 'TL','it_head' => 'IT Head'] as $v => $l)
            <option value="{{ $v }}" {{ old('resolver_level', $user->resolver_level ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Branch</span>
        <select name="branch_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">—</option>
            @foreach($branches as $b)
            <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">State</span>
        <select name="region_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">—</option>
            @foreach($regions as $r)
            <option value="{{ $r->id }}" {{ old('region_id', $user->region_id ?? '') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
            @endforeach
        </select>
    </label>
    @php
        $selectedRegionIds = collect(old('assigned_region_ids',
            ($user ?? null)?->exists ? $user->assignedRegions->pluck('id')->all() : []
        ))->map(fn($v) => (int)$v)->all();
    @endphp
    <div class="md:col-span-2">
        <span class="text-xs font-medium text-gray-500">Auto-route states (resolver only) — pick one or more</span>
        <details class="mt-1 border border-gray-300 rounded">
            <summary class="cursor-pointer px-3 py-2 text-sm text-gray-700 select-none">
                @if(empty($selectedRegionIds))
                    <span class="text-gray-400">— any state —</span>
                @else
                    {{ \App\Models\Region::whereIn('id', $selectedRegionIds)->pluck('name')->join(', ') }}
                @endif
            </summary>
            <div class="px-3 py-2 max-h-56 overflow-auto grid grid-cols-2 gap-1 border-t bg-gray-50">
                @foreach($regions as $r)
                <label class="flex items-center gap-2 text-sm py-1">
                    <input type="checkbox" name="assigned_region_ids[]" value="{{ $r->id }}"
                           {{ in_array($r->id, $selectedRegionIds, true) ? 'checked' : '' }}>
                    <span>{{ $r->name }}</span>
                </label>
                @endforeach
            </div>
        </details>
        <p class="text-[11px] text-gray-500 mt-1">Leave all unchecked to allow tickets from any state.</p>
    </div>
    <label class="block">
        <span class="text-xs font-medium text-gray-500">Auto-route support type (resolver only)</span>
        <select name="assigned_support_type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— any —</option>
            @foreach(['application','infrastructure','admin'] as $t)
            <option value="{{ $t }}" {{ old('assigned_support_type', $user->assigned_support_type ?? '') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </label>
    <label class="flex items-center gap-2 md:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
        <span class="text-sm text-gray-600">Active</span>
    </label>
</div>
<div class="mt-6 flex gap-2">
    <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
    <a href="{{ route('admin.users.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
</div>
