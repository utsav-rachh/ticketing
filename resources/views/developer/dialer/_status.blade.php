@php
    $map = [
        'initiated' => 'bg-slate-500/15 text-slate-300',
        'ringing'   => 'bg-sky-500/15 text-sky-300',
        'answered'  => 'bg-emerald-500/15 text-emerald-300',
        'completed' => 'bg-emerald-500/15 text-emerald-300',
        'missed'    => 'bg-amber-500/15 text-amber-300',
        'busy'      => 'bg-orange-500/15 text-orange-300',
        'failed'    => 'bg-red-500/15 text-red-300',
    ];
    $cls = $map[$status] ?? 'bg-slate-500/15 text-slate-300';
@endphp
<span class="text-xs px-2 py-0.5 rounded-full capitalize {{ $cls }}">{{ $status }}</span>
