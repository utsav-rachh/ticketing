<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} — Sign In</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    <style>
        body { background: #031F2E; font-family: 'Inter', 'DM Sans', sans-serif; }
    </style>
</head>
<body>
<div class="min-h-screen flex items-center justify-center" style="background: radial-gradient(ellipse at 30% 20%, #094A6C 0%, #031F2E 70%);">
    <div class="w-full max-w-md px-8 py-10 rounded-2xl border border-brand-700/30 shadow-2xl" style="background: #06344D;">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-xl mx-auto mb-4 flex items-center justify-center text-2xl font-black text-white shadow-lg" style="background: linear-gradient(135deg, #107AB0, #D4A843);">AC</div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Altum Credo</h1>
            <p class="text-sm text-gold-400 mt-1 font-medium">IT Service Management Portal</p>
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
                    class="w-full px-4 py-3 rounded-lg text-sm text-white border border-brand-700/50 focus:border-brand-400 focus:ring-1 focus:ring-brand-400 focus:outline-none transition-colors"
                    style="background: #094A6C;"
                    placeholder="you@altumcredo.com">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Password</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-4 py-3 rounded-lg text-sm text-white border border-brand-700/50 focus:border-brand-400 focus:ring-1 focus:ring-brand-400 focus:outline-none transition-colors"
                    style="background: #094A6C;"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full py-3 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:shadow-lg"
                style="background: linear-gradient(135deg, #107AB0, #0C5F8A);">
                Sign In
            </button>
        </form>

        <div class="mt-8 text-center">
            <p class="text-[11px] text-gray-500">Developed by <span class="text-gold-400/70 font-semibold">5ap Media</span></p>
        </div>

    </div>
</div>
</body>
</html>
