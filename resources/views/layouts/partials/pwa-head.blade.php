{{--
    WMC CAISSE — Balises PWA pour le <head>
    Inclure dans layouts/app.blade.php et layouts/guest.blade.php
--}}
@php
    $pwaThemeColor = '#F59E0B';
    $pwaAppName = config('app.name', 'GestionCaisse WMC');
@endphp

{{-- Manifest PWA --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">

{{-- Couleur de la barre d'état (Android / Chrome) --}}
<meta name="theme-color" content="{{ $pwaThemeColor }}">
<meta name="mobile-web-app-capable" content="yes">

{{-- iOS / Safari --}}
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="WMC Caisse">
<link rel="apple-touch-icon" href="{{ asset('images/logos/logo_wmc_orange.png') }}">

{{-- Microsoft / Windows --}}
<meta name="msapplication-TileColor" content="{{ $pwaThemeColor }}">
<meta name="msapplication-TileImage" content="{{ asset('images/logos/logo_wmc_orange.png') }}">

{{-- Description pour les moteurs et installateurs --}}
<meta name="application-name" content="{{ $pwaAppName }}">
<meta name="description" content="Gestion de caisse WMC — ventes, stock, clients et paiements. Fonctionne hors connexion.">
