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
    <body class="font-sans text-gray-900 antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-4">
                <a href="/">
                    <img src="{{ asset('images/altumcredo_logo.png') }}" alt="Altum Credo" class="h-14 bg-white rounded-lg p-1.5 object-contain shadow">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 px-6 py-6 bg-white shadow-lg overflow-hidden sm:rounded-xl border border-gray-200">
                {{ $slot }}
            </div>

            <div class="mt-6">
                <p class="text-[11px] text-gray-400">Developed by <span class="font-semibold" style="color: #0056B3;">5P Media</span></p>
            </div>
        </div>
    </body>
</html>
