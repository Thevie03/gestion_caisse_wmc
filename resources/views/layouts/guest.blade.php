<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="color-scheme: light; height: 100%; width: 100%;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Forcer le thème clair même si le navigateur est en mode sombre -->
    <meta name="color-scheme" content="light">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    @php
        $cssVersion = file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time();
    @endphp
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ $cssVersion }}">

    <!-- Styles critiques inline pour garantir l'affichage -->
    <style>
        /* Styles critiques pour la page de login - garantissent l'affichage même si le CSS externe ne se charge pas */
        .login-wrapper {
            display: flex !important;
            min-height: 100vh !important;
            width: 100% !important;
            overflow: hidden !important;
        }

        .login-left-panel {
            flex: 0 0 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .login-right-panel {
            flex: 0 0 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
        }

        @media (max-width: 991.98px) {
            .login-wrapper {
                flex-direction: column !important;
            }

            .login-left-panel,
            .login-right-panel {
                flex: 1 1 auto !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .login-right-panel {
                order: -1 !important;
                min-height: 40vh !important;
                max-height: 40vh !important;
            }
        }

        @media (max-width: 575.98px) {
            .login-right-panel {
                min-height: 30vh !important;
                max-height: 30vh !important;
            }
        }

        /* Light theme for welcome page */
        body:has(.welcome-wrapper) {
            background: #f8f9fa !important;
        }
    </style>

</head>

<body class="font-sans text-gray-900 antialiased"
    style="margin: 0; padding: 0; overflow-x: hidden; height: 100%; width: 100%;">
    {{ $slot }}

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous">
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" crossorigin="anonymous"></script>
</body>

</html>
