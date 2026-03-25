<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Sign In</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0B0F1A; font-family: 'Inter', 'DM Sans', sans-serif; }
    </style>
</head>
<body>
<div class="min-h-screen flex items-center justify-center" style="background: radial-gradient(ellipse at 30% 20%, #0F2847 0%, #0B0F1A 70%);">
    <div class="w-full max-w-md px-8 py-10 rounded-2xl border border-gray-700 shadow-2xl" style="background:#111827;">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-xl mx-auto mb-4 flex items-center justify-center text-2xl font-black text-white" style="background: linear-gradient(135deg, #3B82F6, #D4A843);">AC</div>
            <h1 class="text-xl font-bold text-white">Altum Credo Finance</h1>
            <p class="text-sm text-gray-400 mt-1">IT Service Management Portal</p>
        </div>

        <!-- Session Status -->
        @if(session('status'))
        <div class="mb-4 text-sm text-green-400 text-center">{{ session('status') }}</div>
        @endif

        <!-- Errors -->
        @if($errors->any())
        <div class="mb-4 text-sm text-red-400 text-center">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 rounded-lg text-sm text-white border border-gray-600 focus:border-blue-500 focus:outline-none transition-colors"
                    style="background:#1A2236;"
                    placeholder="you@altumcredo.com">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Password</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg text-sm text-white border border-gray-600 focus:border-blue-500 focus:outline-none transition-colors"
                    style="background:#1A2236;"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full py-3 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                Sign In
            </button>
        </form>


    </div>
</div>
</body>
</html>
