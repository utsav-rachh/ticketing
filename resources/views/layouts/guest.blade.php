<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Altum Credo') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="background: #031F2E;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background: radial-gradient(ellipse at 30% 20%, #094A6C 0%, #031F2E 70%);">
            <div class="mb-4">
                <a href="/" class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-xl font-black text-white shadow-lg" style="background: linear-gradient(135deg, #107AB0, #D4A843);">AC</div>
                    <span class="text-lg font-bold text-white">Altum Credo</span>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-6 py-6 shadow-xl overflow-hidden sm:rounded-xl border border-brand-700/30" style="background: #06344D;">
                {{ $slot }}
            </div>

            <div class="mt-6">
                <p class="text-[11px] text-gray-500">Developed by <span class="text-gold-400/70 font-semibold">5ap Media</span></p>
            </div>
        </div>
    </body>
</html>
