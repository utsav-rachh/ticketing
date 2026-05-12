@php
    $map = [
        'initiated' => 'bg-gray-100 text-gray-600',
        'ringing'   => 'bg-blue-100 text-blue-700',
        'answered'  => 'bg-green-100 text-green-700',
        'completed' => 'bg-green-100 text-green-700',
        'missed'    => 'bg-amber-100 text-amber-700',
        'busy'      => 'bg-orange-100 text-orange-700',
        'failed'    => 'bg-red-100 text-red-700',
    ];
    $cls = $map[$status] ?? 'bg-gray-100 text-gray-600';
@endphp
<span class="text-xs px-2 py-0.5 rounded-full capitalize {{ $cls }}">{{ $status }}</span>
