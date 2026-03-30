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
</head>
<body class="font-sans antialiased bg-white">
<div class="min-h-screen flex">

    <!-- Left Panel — Branding -->
    <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-12 text-white" style="background: linear-gradient(135deg, #002E52 0%, #0056B3 100%);">
        <div>
            <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-12 bg-white rounded-lg p-1.5 object-contain">
        </div>
        <div class="max-w-md">
            <h1 class="text-4xl font-bold leading-tight mb-6">Ticketing System</h1>
            <p class="text-lg text-white/80 leading-relaxed mb-8">Streamline your support operations with a centralized platform for tracking, managing, and resolving tickets efficiently.</p>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold">Create & Track Tickets</p>
                        <p class="text-sm text-white/60">Raise issues and follow them through to resolution</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold">Team Collaboration</p>
                        <p class="text-sm text-white/60">Assign, prioritize, and resolve tickets as a team</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold">Reports & Insights</p>
                        <p class="text-sm text-white/60">Monitor performance with detailed analytics</p>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <p class="text-xs text-white/40">Developed by <span class="text-white/60 font-semibold">5P Media</span></p>
        </div>
    </div>

    <!-- Right Panel — Login Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-md">

            <!-- Mobile logo -->
            <div class="lg:hidden mb-8 text-center">
                <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-14 mx-auto bg-white rounded-lg p-1.5 object-contain shadow">
            </div>

            <h2 class="text-2xl font-bold mb-1" style="color: #002E52;">Welcome back</h2>
            <p class="text-sm text-gray-500 mb-8">Sign in to your account to continue</p>

            <!-- Session Status -->
            @if(session('status'))
            <div class="mb-4 text-sm text-green-600 bg-green-50 border border-green-200 px-4 py-2 rounded-lg">{{ session('status') }}</div>
            @endif

            <!-- Errors -->
            @if($errors->any())
            <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 px-4 py-2 rounded-lg">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-lg text-sm border border-gray-300 focus:ring-2 focus:outline-none transition-colors"
                        style="focus-color: #0056B3;"
                        placeholder="you@altumcredo.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg text-sm border border-gray-300 focus:ring-2 focus:outline-none transition-colors"
                        placeholder="Enter your password">
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-lg text-sm font-semibold text-white transition-all duration-200 hover:opacity-90 hover:shadow-lg"
                    style="background: #0056B3;">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center lg:hidden">
                <p class="text-xs text-gray-400">Developed by <span class="font-semibold" style="color: #0056B3;">5P Media</span></p>
            </div>

        </div>
    </div>

</div>
</body>
</html>
