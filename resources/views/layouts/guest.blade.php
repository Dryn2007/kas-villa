<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kas Villa - Sistem Pembayaran Kel H Solichin</title>

    <!-- Meta Tags (Open Graph) untuk WhatsApp, Facebook, dll -->
    <meta property="og:title" content="Kas Villa - Sistem Pembayaran Kel H Solichin">
    <meta property="og:description"
        content="Portal transparansi dan kemudahan pembayaran iuran Kel H Solichin secara digital.">
    <meta property="og:image" content="{{ asset('images/Thumbnail.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Card untuk memastikan format besar -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Kas Villa - Sistem Pembayaran Kel H Solichin">
    <meta name="twitter:description"
        content="Portal transparansi dan kemudahan pembayaran iuran Kel H Solichin secara digital.">
    <meta name="twitter:image" content="{{ asset('images/Thumbnail.png') }}">

    <!-- Favicon (logo web) -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-gray-900 antialiased">
    {{ $slot }}
</body>

</html>