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

    <title inertia>{{ $settings['app_name'] }}</title>

    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @if (!empty($settings['app_favicon']))
        <link rel="icon" href="{{ $settings['app_favicon'] }}" sizes="any">
    @endif

    @routes
    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased" style="margin: 0 !important;">
    @inertia
</body>

</html>
