@csrf
@if(($project ?? null)?->exists) @method('PATCH') @endif
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <label class="block md:col-span-2">
        <span class="text-xs font-medium text-gray-500">Name</span>
        <input type="text" name="name" required maxlength="200"
               value="{{ old('name', $project->name ?? '') }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>

    <label class="block md:col-span-2">
        <span class="text-xs font-medium text-gray-500">Description</span>
        <textarea name="description" rows="3" maxlength="5000"
                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm">{{ old('description', $project->description ?? '') }}</textarea>
    </label>

    <label class="block">
        <span class="text-xs font-medium text-gray-500">Owner (management user)</span>
        <select name="owner_id" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            <option value="">— Select —</option>
            @foreach($owners as $o)
            <option value="{{ $o->id }}" {{ old('owner_id', $project->owner_id ?? '') == $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="block">
        <span class="text-xs font-medium text-gray-500">Status</span>
        <select name="status" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
            @foreach(['active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed'] as $v => $l)
            <option value="{{ $v }}" {{ old('status', $project->status ?? 'active') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </label>

    <label class="block">
        <span class="text-xs font-medium text-gray-500">Start Date</span>
        <input type="date" name="start_date"
               value="{{ old('start_date', optional($project->start_date ?? null)->format('Y-m-d')) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>

    <label class="block">
        <span class="text-xs font-medium text-gray-500">End Date</span>
        <input type="date" name="end_date"
               value="{{ old('end_date', optional($project->end_date ?? null)->format('Y-m-d')) }}"
               class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
    </label>
</div>

<div class="mt-6 flex gap-2">
    <button type="submit" class="text-white px-5 py-2 rounded text-sm font-medium" style="background:#0056B3;">Save</button>
    <a href="{{ route('projects.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded text-sm">Cancel</a>
</div>
