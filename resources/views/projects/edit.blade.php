@extends('layouts.app')
@section('title', 'Edit Project')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-700">Edit Project · {{ $project->number }}</h2>
        <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Archive this project? Linked tickets will keep their reference.');">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-600 hover:underline">Archive</button>
        </form>
    </div>
    <form method="POST" action="{{ route('projects.update', $project) }}">
        @include('projects._form')
    </form>
</div>
@endsection
