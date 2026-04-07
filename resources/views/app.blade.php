<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- SEO defaults — overridden per-page via Inertia Head when needed --}}
        <meta data-inertia="description" name="description" content="Open-source luxury real estate template built with Laravel 13, Vue 3, Inertia.js v3, and shadcn-vue. Production-ready, SOLID, fully tested.">

        {{-- Authorship --}}
        <meta name="author" content="Igor Ponso">
        <meta name="generator" content="Laravel 13 + Vue 3 + Inertia.js v3">
        <link rel="author" href="https://github.com/Igor-Ponso">
        <meta name="copyright" content="Igor Ponso — MIT License">

        {{-- Open Graph defaults --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Sovereign Estates">
        <meta data-inertia="og:title" property="og:title" content="Sovereign Estates — Real Estate Template">
        <meta data-inertia="og:description" property="og:description" content="Open-source luxury real estate template built with Laravel 13, Vue 3, Inertia.js v3, and shadcn-vue.">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta data-inertia="twitter:title" name="twitter:title" content="Sovereign Estates — Real Estate Template">
        <meta data-inertia="twitter:description" name="twitter:description" content="Open-source luxury real estate template built with Laravel 13, Vue 3, Inertia.js v3, and shadcn-vue.">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
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
        <style>
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

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="preconnect" href="https://a.basemaps.cartocdn.com" crossorigin>
        <link rel="preconnect" href="https://b.basemaps.cartocdn.com" crossorigin>
        <link rel="preconnect" href="https://c.basemaps.cartocdn.com" crossorigin>
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600|playfair-display:400,500,600,700|inter:300,400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
