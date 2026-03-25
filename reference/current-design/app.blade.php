<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="SDB Wallet — أول محفظة إلكترونية سورية. حسابات متعددة العملات، بطاقات ماستركارد، تحويلات دولية، عملات رقمية. مرخّص في أوروبا.">
    <meta name="theme-color" content="#1B5E20">
    <meta name="facebook-domain-verification" content="cg981rikj24zq0gdiwjuggu7x6i6ei" />
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/favicon-512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SDB Wallet">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://sdbwallet.com/">
    <meta property="og:title" content="SDB Wallet — أول محفظة إلكترونية سورية">
    <meta property="og:description"
        content="حوّل، ادفع، ووفّر بدون رسوم مخفية. حسابات متعددة العملات، بطاقات ماستركارد فورية، تحويلات لـ 150+ دولة.">
    <meta property="og:image" content="https://sdbwallet.com/images/og-cover.png">
    <meta property="og:locale" content="ar_SY">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="SDB Wallet — أول محفظة إلكترونية سورية">
    <meta name="twitter:description" content="حوّل، ادفع، ووفّر بدون رسوم مخفية. مرخّص في أوروبا 🇪🇺">
    <meta name="twitter:image" content="https://sdbwallet.com/images/og-cover.png">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts (preload for performance) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Multi-language SEO -->
    <link rel="alternate" hreflang="ar" href="https://sdbwallet.com/">
    <link rel="alternate" hreflang="en" href="https://sdbwallet.com/">
    <link rel="alternate" hreflang="x-default" href="https://sdbwallet.com/">

    <!-- Canonical -->
    <link rel="canonical" href="https://sdbwallet.com/">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js').catch(() => { }); }
    </script>

    <!-- Analytics (Plausible — privacy-friendly, no cookies) -->
    <script defer data-domain="sdbwallet.com" src="https://plausible.io/js/script.js"></script>
</body>

</html>