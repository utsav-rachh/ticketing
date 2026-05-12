{{--
    Shared dashboard ticket table — used by the Management, Project and Recent
    sections so they keep the same columns and quick-assign behaviour.

    Expects:
      $tickets          Collection of Ticket
      $canQuickAssign   bool
      $assignableUsers  Collection
      $emptyMessage     string (HTML allowed) shown when $tickets is empty
      $withProject      bool (optional) — adds a "Project" column
--}}
@php
    $withProject = $withProject ?? false;
    $colCount    = $withProject ? 11 : 10;
@endphp
<table class="w-full text-sm" data-mobile="cards">
    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
        <tr>
            <th class="px-4 py-3 text-left">Ticket #</th>
            <th class="px-4 py-3 text-left">Subject / Issue</th>
            <th class="px-4 py-3 text-left">Category</th>
            @if($withProject)<th class="px-4 py-3 text-left">Project</th>@endif
            <th class="px-4 py-3 text-left">Priority</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Branch / State</th>
            <th class="px-4 py-3 text-left">Raised By</th>
            <th class="px-4 py-3 text-left">Assignee</th>
            <th class="px-4 py-3 text-left">TAT</th>
            <th class="px-4 py-3 text-left">Age</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @forelse($tickets as $ticket)
        @php
            $isViolated = $ticket->is_tat_violated && !in_array($ticket->status, ['resolved','closed']);
            $rowClass   = $isViolated
                ? 'bg-red-100 hover:bg-red-200 border-l-4 border-red-500'
                : ($ticket->is_red_flag ? 'bg-red-50 hover:bg-red-100' : 'hover:bg-gray-50');
        @endphp
        <tr class="{{ $rowClass }}">
            <td class="px-4 py-3 align-top">
                <a href="{{ route('tickets.show', $ticket) }}" class="text-brand-500 hover:underline font-mono text-xs inline-flex items-center gap-1">
                    @if($ticket->is_red_flag)
                    <svg class="w-3.5 h-3.5 text-red-600" fill="currentColor" viewBox="0 0 20 20" title="Red-flagged"><path d="M3 4a1 1 0 011-1h4.586A1 1 0 019.293 3.293L10 4h5a1 1 0 011 1v7a1 1 0 01-1 1h-5.586a1 1 0 01-.707-.293L9 12H4v5a1 1 0 11-2 0V4z"/></svg>
                    @endif
                    {{ $ticket->ticket_number }}
                </a>
                @if($isViolated)
                <div class="mt-1"><span class="bg-red-600 text-white text-[8px] font-bold px-1 py-0.5 rounded">TAT VIOLATED</span></div>
                @endif
                <div class="text-[10px] text-gray-400 mt-0.5 uppercase">{{ ucfirst($ticket->support_type) }}</div>
            </td>
            <td class="px-4 py-3 align-top max-w-xs">
                <div class="truncate font-medium text-gray-800">{{ $ticket->subject }}</div>
                <div class="text-xs text-gray-500 truncate">{{ $ticket->subcategory->name ?? ($ticket->custom_issue ?? '—') }}</div>
            </td>
            <td class="px-4 py-3 align-top text-xs text-gray-600">{{ $ticket->category->name ?? '—' }}</td>
            @if($withProject)
            <td class="px-4 py-3 align-top text-xs">
                @if($ticket->project)
                    <a href="{{ route('projects.show', $ticket->project) }}" class="text-brand-500 hover:underline">{{ $ticket->project->name }}</a>
                    <div class="text-[10px] text-gray-400 font-mono">{{ $ticket->project->number }}</div>
                @else
                    <span class="text-gray-400">—</span>
                @endif
            </td>
            @endif
            <td class="px-4 py-3 align-top">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $ticket->priority === 'critical' ? 'bg-red-100 text-red-700' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-700' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700')) }}">
                    {{ ucfirst($ticket->priority) }}
                </span>
            </td>
            <td class="px-4 py-3 align-top">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
            </td>
            <td class="px-4 py-3 align-top text-xs text-gray-600">
                {{ $ticket->branch->name ?? '—' }}<br>
                <span class="text-gray-400">{{ $ticket->branch->region->name ?? '—' }}</span>
            </td>
            <td class="px-4 py-3 align-top text-gray-600">
                <div class="text-xs">{{ $ticket->creator->name ?? '—' }}</div>
                <div class="text-[10px] text-gray-400">{{ $ticket->employee_contact_phone ?? '' }}</div>
            </td>
            <td class="px-4 py-3 align-top text-gray-600">
                @if($canQuickAssign && !in_array($ticket->status, ['resolved','closed']) && $assignableUsers->isNotEmpty())
                <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="flex items-center gap-1">
                    @csrf
                    <select name="assigned_to" class="border border-gray-300 rounded px-1.5 py-1 text-xs bg-white w-32" onchange="if(this.value) this.form.submit()">
                        <option value="" disabled {{ $ticket->assigned_to ? '' : 'selected' }}>— Assign —</option>
                        @foreach($assignableUsers as $u)
                        <option value="{{ $u->id }}" {{ $ticket->assigned_to == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </form>
                @else
                    <span class="text-xs">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                @endif
            </td>
            <td class="px-4 py-3 align-top text-xs">
                @if(in_array($ticket->status, ['resolved','closed']))
                    <span class="text-green-600">✓ done</span>
                @elseif($ticket->isOverdue())
                    <span class="text-red-600 font-semibold">Violated</span>
                @else
                    @php $p = $ticket->tatProgress(); @endphp
                    <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 {{ $p >= 75 ? 'bg-yellow-500' : 'bg-green-500' }}" style="width: {{ $p }}%"></div>
                    </div>
                    <span class="text-[10px] text-gray-500">{{ $p }}%</span>
                @endif
            </td>
            <td class="px-4 py-3 align-top text-gray-400 text-xs">{{ $ticket->created_at->diffForHumans() }}</td>
        </tr>
        @empty
        <tr><td colspan="{{ $colCount }}" class="px-6 py-8 text-center text-gray-400">{!! $emptyMessage !!}</td></tr>
        @endforelse
    </tbody>
</table>
