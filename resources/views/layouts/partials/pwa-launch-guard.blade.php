{{--
    Garde-fou PWA mobile / desktop (standalone).
    Si l'app est ouverte sans session (ex. raccourci iOS créé sur /dashboard),
    redirige vers le point d'entrée public /app → login.
--}}
<script>
    (function () {
        'use strict';

        var isStandalone = window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;

        if (!isStandalone) {
            return;
        }

        var path = window.location.pathname.replace(/\/+$/, '') || '/';
        var publicPaths = ['/', '/login', '/register', '/app', '/offline.html', '/welcome'];

        if (publicPaths.indexOf(path) !== -1) {
            return;
        }

        fetch('{{ url('/csrf-token') }}', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(function (response) {
            if (response.status === 401 || response.status === 403 || response.status === 419) {
                window.location.replace('{{ url('/app') }}');
            }
        }).catch(function () {
            /* hors ligne : le service worker gère le fallback */
        });
    })();
</script>
