{{--
    WMC CAISSE — Balises PWA pour le <head>
    Inclure dans layouts/app.blade.php et layouts/guest.blade.php
--}}
@php
    $pwaThemeColor = '#F59E0B';
    $pwaAppName = config('app.name', 'GestionCaisse WMC');
@endphp

{{-- Manifest PWA --}}
<link rel="manifest" href="{{ url('/manifest.json') }}?v=1.6.0">

{{-- Icônes Android / Chrome --}}
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/icons/icon-512.png') }}">

{{-- Couleur de la barre d'état (Android / Chrome) --}}
<meta name="theme-color" content="{{ $pwaThemeColor }}">
<meta name="mobile-web-app-capable" content="yes">

{{-- iOS / Safari --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="WMC Caisse">
<link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">

{{-- Microsoft / Windows --}}
<meta name="msapplication-TileColor" content="{{ $pwaThemeColor }}">
<meta name="msapplication-TileImage" content="{{ asset('images/icons/icon-512.png') }}">

{{-- Description pour les moteurs et installateurs --}}
<meta name="application-name" content="{{ $pwaAppName }}">
<meta name="description" content="Gestion de caisse WMC — ventes, stock, clients et paiements. Fonctionne hors connexion.">

{{-- Redirection auto si app installée ouverte sans session (iOS/Android/desktop) --}}
@include('layouts.partials.pwa-launch-guard')
