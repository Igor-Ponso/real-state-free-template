<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- SEO defaults — pure static tags, not managed by Inertia --}}
        <meta name="description" content="Open-source luxury real estate template built with Laravel 13, Vue 3, Inertia.js v3, and shadcn-vue. Production-ready, SOLID, fully tested.">

        {{-- Authorship --}}
        <meta name="author" content="Igor Ponso">
        <meta name="generator" content="Laravel 13 + Vue 3 + Inertia.js v3">
        <link rel="author" href="https://github.com/Igor-Ponso">
        <meta name="copyright" content="Igor Ponso — MIT License">

        {{-- Open Graph defaults --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Sovereign Estates">
        <meta property="og:title" content="Sovereign Estates — Real Estate Template">
        <meta property="og:description" content="Open-source luxury real estate template built with Laravel 13, Vue 3, Inertia.js v3, and shadcn-vue.">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Sovereign Estates — Real Estate Template">
        <meta name="twitter:description" content="Open-source luxury real estate template built with Laravel 13, Vue 3, Inertia.js v3, and shadcn-vue.">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script @if(app()->bound(\Illuminate\Support\Facades\Vite::class)) nonce="{{ Vite::cspNonce() }}" @endif>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style @if(app()->bound(\Illuminate\Support\Facades\Vite::class)) nonce="{{ Vite::cspNonce() }}" @endif>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        {{-- Preload hero poster — makes it the LCP element on the landing page --}}
        <link rel="preload" as="image" href="/images/auth/luxury-real-estate-poster.jpg" fetchpriority="high">

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
