<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 sm:py-0 bg-gradient-to-b from-brand-800 via-brand-700 to-brand-900">
            <div class="mb-6">
                <a href="/" wire:navigate>
                    <x-application-logo class="w-16 h-16 sm:w-20 sm:h-20 fill-current text-white" />
                </a>
            </div>

            <div class="w-full max-w-md px-6 py-8 sm:px-8 bg-white shadow-xl overflow-hidden rounded-xl">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-brand-200 text-center">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}
            </p>
        </div>
    </body>
</html>
