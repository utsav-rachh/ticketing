@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-bold text-gray-700 mb-4">Edit User — {{ $user->name }}</h2>
    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @include('admin.users._form', ['user' => $user])
    </form>
    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-4 border-t pt-4">
        @csrf @method('DELETE')
        <button class="text-red-600 text-sm hover:underline" onclick="return confirm('Deactivate this user?')">Deactivate user</button>
    </form>
</div>
@endsection
