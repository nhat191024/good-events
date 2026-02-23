<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1 maximum-scale=1">

    <!-- Google tag (gtag.js) -->
    @production
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-NSC22EQJDL"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());

            gtag('config', 'G-NSC22EQJDL');
        </script>

        <meta name="google-site-verification" content="xzQBSYjswFBEVPucgfh7szJ9LdS2z7BMZ7kgRFBn7GQ" />
    @endproduction

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? 'system' }}';

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

    <link type="image/png" rel="icon" href="/favicon-96x96.png" sizes="96x96" />
    <link type="image/svg+xml" rel="icon" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Sự Kiện Tốt" />
    <link rel="manifest" href="/site.webmanifest" />

    @if (!empty($settings['app_favicon']))
        <link rel="icon" href="{{ $settings['app_favicon'] }}" sizes="any">
    @endif

    @routes
    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased" style="margin: 0 !important;">
    <x-impersonate-banner />
    @inertia
</body>

</html>
