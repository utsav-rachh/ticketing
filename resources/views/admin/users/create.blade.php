@extends('layouts.app')
@section('title', 'New User')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-4 md:p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">New User</h2>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @include('admin.users._form', ['user' => $user ?? null])
    </form>
</div>
@endsection
