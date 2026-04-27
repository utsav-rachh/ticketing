<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $ticket->ticket_number }}</title>
    <style>
        @page { margin: 22mm 14mm 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #1F2937; line-height: 1.45; }
        .brand-band { background: #002E52; color: #fff; padding: 10px 12px; margin: -12mm -14mm 14px -14mm; }
        .brand-band h1 { margin: 0; font-size: 14px; letter-spacing: 0.5px; }
        .brand-band .sub { font-size: 9.5px; opacity: 0.85; margin-top: 2px; }
        h2 { font-size: 12px; margin: 16px 0 6px 0; color: #002E52; border-bottom: 1px solid #D1D5DB; padding-bottom: 3px; }
        h3 { font-size: 11px; margin: 12px 0 4px 0; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        td, th { vertical-align: top; padding: 4px 6px; }
        .meta-table td { border-bottom: 1px solid #F3F4F6; font-size: 10px; }
        .meta-table td.label { color: #6B7280; width: 28%; }
        .meta-table td.value { color: #111827; font-weight: 500; }
        .pill { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
        .pill-red       { background: #FEE2E2; color: #B91C1C; }
        .pill-orange    { background: #FFEDD5; color: #C2410C; }
        .pill-yellow    { background: #FEF3C7; color: #92400E; }
        .pill-green     { background: #DCFCE7; color: #166534; }
        .pill-gray      { background: #F3F4F6; color: #374151; }
        .pill-blue      { background: #DBEAFE; color: #1D4ED8; }
        .pill-purple    { background: #EDE9FE; color: #6D28D9; }
        .red-flag-bar   { background: #FEE2E2; border: 1px solid #FCA5A5; padding: 6px 10px; margin-bottom: 10px; color: #991B1B; font-size: 10px; }
        .tat-bar        { background: #DC2626; color: #fff; padding: 6px 10px; margin-bottom: 10px; font-size: 10px; font-weight: 600; }
        .timeline-row   { border-bottom: 1px solid #F3F4F6; padding: 6px 0; }
        .timeline-row:last-child { border-bottom: 0; }
        .timeline-meta  { color: #6B7280; font-size: 9.5px; }
        .timeline-text  { color: #111827; font-size: 10px; margin-top: 2px; white-space: pre-wrap; }
        .small { font-size: 9.5px; color: #6B7280; }
        .footer { position: fixed; bottom: 8mm; left: 14mm; right: 14mm; font-size: 8.5px; color: #9CA3AF; border-top: 1px solid #E5E7EB; padding-top: 4px; }
        .grid-2 { display: table; width: 100%; }
        .grid-2 .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 6px; }
        .desc-block { background: #F9FAFB; padding: 8px 10px; border-left: 3px solid #0056B3; font-size: 10px; white-space: pre-wrap; }
    </style>
</head>
<body>

<div class="brand-band">
    <h1>{{ config('app.company_name', 'Altum Credo Finance Private Limited') }} — Ticket Report</h1>
    <div class="sub">Ticket #{{ $ticket->ticket_number }} &middot; Generated {{ $generatedAt->format('d M Y, H:i') }} by {{ $generatedBy->name ?? 'system' }}</div>
</div>

@if($ticket->is_red_flag)
<div class="red-flag-bar"><strong>⚑ Red Flag — Management Ticket.</strong> Treat as highest priority.</div>
@endif

@if($ticket->is_tat_violated && !in_array($ticket->status, ['resolved','closed']))
<div class="tat-bar">⚠ TAT VIOLATED — deadline {{ $ticket->tat_deadline?->format('d M Y, H:i') }}</div>
@endif

<h2>Summary</h2>
<table>
    <tr>
        <td style="width: 70%; padding-left: 0;">
            <div style="font-size: 13px; font-weight: 700; color: #111827;">{{ $ticket->subject }}</div>
            <div class="small" style="margin-top: 2px;">
                {{ $ticket->category->name ?? '—' }} &raquo; {{ $ticket->subcategory->name ?? '—' }}
                @if($ticket->custom_issue) ({{ $ticket->custom_issue }}) @endif
                &middot; {{ ucfirst($ticket->support_type) }}
            </div>
        </td>
        <td style="width: 30%; text-align: right; padding-right: 0;">
            @php
                $priorityClass = match($ticket->priority) {
                    'critical' => 'pill-red', 'high' => 'pill-orange', 'medium' => 'pill-yellow', default => 'pill-green',
                };
                $statusClass = match($ticket->status) {
                    'open' => 'pill-blue','assigned' => 'pill-blue','in_progress' => 'pill-yellow',
                    'pending_info' => 'pill-orange','hold' => 'pill-purple','resolved' => 'pill-green',
                    'closed' => 'pill-gray', default => 'pill-gray',
                };
            @endphp
            <span class="pill {{ $priorityClass }}">{{ $ticket->priority }}</span>
            <span class="pill {{ $statusClass }}">{{ str_replace('_', ' ', $ticket->status) }}</span>
        </td>
    </tr>
</table>

@if($ticket->description)
<h3>Description</h3>
<div class="desc-block">{{ $ticket->description }}</div>
@endif

<h2>Details</h2>
<div class="grid-2">
    <div class="col">
        <table class="meta-table">
            <tr><td class="label">Raised By</td><td class="value">{{ $ticket->creator->name ?? '—' }}</td></tr>
            <tr><td class="label">Employee ID</td><td class="value">{{ $ticket->employee_contact_employee_id ?: '—' }}</td></tr>
            <tr><td class="label">Phone</td><td class="value">{{ $ticket->employee_contact_phone ?: '—' }}</td></tr>
            <tr><td class="label">Email</td><td class="value">{{ $ticket->employee_contact_email ?: '—' }}</td></tr>
            <tr><td class="label">Branch</td><td class="value">{{ $ticket->branch->name ?? '—' }}</td></tr>
            <tr><td class="label">State</td><td class="value">{{ $ticket->branch->region->name ?? '—' }}</td></tr>
        </table>
    </div>
    <div class="col">
        <table class="meta-table">
            <tr><td class="label">Assigned To</td><td class="value">{{ $ticket->assignee->name ?? 'Unassigned' }}</td></tr>
            @if($ticket->vendor)
            <tr><td class="label">Vendor</td><td class="value">{{ $ticket->vendor->name }}</td></tr>
            <tr><td class="label">Vendor Ref</td><td class="value">{{ $ticket->vendor_reference ?: '—' }}</td></tr>
            @endif
            <tr><td class="label">Created</td><td class="value">{{ $ticket->created_at->format('d M Y, H:i') }}</td></tr>
            <tr><td class="label">TAT Deadline</td><td class="value">{{ $ticket->tat_deadline?->format('d M Y, H:i') }} ({{ $ticket->tat_hours }}h)</td></tr>
            @if($ticket->resolved_at)
            <tr><td class="label">Resolved</td><td class="value">{{ $ticket->resolved_at->format('d M Y, H:i') }}</td></tr>
            @endif
            @if($ticket->closed_at)
            <tr><td class="label">Closed</td><td class="value">{{ $ticket->closed_at->format('d M Y, H:i') }}</td></tr>
            @endif
            @if($ticket->hold_total_seconds > 0)
            <tr><td class="label">Total Hold Time</td><td class="value">{{ gmdate('H:i', $ticket->hold_total_seconds) }} h</td></tr>
            @endif
        </table>
    </div>
</div>

<h2>Activity Timeline</h2>
@forelse($timeline as $i => $item)
<div class="timeline-row">
    <table>
        <tr>
            <td style="width: 60%; padding-left: 0;">
                <strong>{{ $item->user->name ?? 'System' }}</strong>
                @if($item->old || $item->new)
                <span class="small">
                    &middot; status:
                    <span class="pill pill-gray">{{ $item->old ?: '—' }}</span>
                    &rarr;
                    <span class="pill pill-blue">{{ $item->new ?: '—' }}</span>
                </span>
                @elseif($item->type && $item->type !== 'update')
                <span class="small">&middot; {{ str_replace('_', ' ', $item->type) }}</span>
                @endif
            </td>
            <td style="width: 40%; text-align: right; padding-right: 0;" class="small">
                {{ $item->at?->format('d M Y, H:i') }}
            </td>
        </tr>
    </table>
    @if($item->text)
    <div class="timeline-text">{{ $item->text }}</div>
    @endif
</div>
@empty
<div class="small">No activity recorded.</div>
@endforelse

@if($ticket->expenses->isNotEmpty())
<h2>Expenses</h2>
<table>
    <thead>
        <tr style="background: #F9FAFB; font-size: 9.5px; color: #374151;">
            <th style="text-align: left; border-bottom: 1px solid #E5E7EB;">Date</th>
            <th style="text-align: left; border-bottom: 1px solid #E5E7EB;">Description</th>
            <th style="text-align: left; border-bottom: 1px solid #E5E7EB;">Submitted by</th>
            <th style="text-align: left; border-bottom: 1px solid #E5E7EB;">Status</th>
            <th style="text-align: right; border-bottom: 1px solid #E5E7EB;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php $totalApproved = 0; @endphp
        @foreach($ticket->expenses as $exp)
        @php if ($exp->status === 'approved') $totalApproved += (float) $exp->amount; @endphp
        <tr style="border-bottom: 1px solid #F3F4F6;">
            <td>{{ $exp->expense_date?->format('d M Y') }}</td>
            <td>{{ $exp->description }}</td>
            <td>{{ $exp->addedBy->name ?? '—' }}</td>
            <td><span class="pill {{ $exp->status === 'approved' ? 'pill-green' : ($exp->status === 'rejected' ? 'pill-red' : 'pill-yellow') }}">{{ $exp->status }}</span></td>
            <td style="text-align: right;">&#8377; {{ number_format((float) $exp->amount, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="4" style="text-align: right; font-weight: 700;">Approved total</td>
            <td style="text-align: right; font-weight: 700;">&#8377; {{ number_format($totalApproved, 2) }}</td>
        </tr>
    </tbody>
</table>
@endif

<div class="footer">
    {{ config('app.company_name', 'Altum Credo Finance Private Limited') }} &middot; Internal ticket report &middot; {{ $generatedAt->format('d M Y, H:i') }}
</div>

</body>
</html>
