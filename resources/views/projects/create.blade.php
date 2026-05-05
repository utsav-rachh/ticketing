@extends('layouts.app')
@section('title', 'New Project')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">New Project</h2>
    <form method="POST" action="{{ route('projects.store') }}">
        @include('projects._form')
    </form>
</div>
@endsection
