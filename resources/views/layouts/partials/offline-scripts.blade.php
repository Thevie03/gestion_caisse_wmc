{{--
    WMC CAISSE — Scripts du mode hors connexion (Étape 2)
    Charger uniquement pour les utilisateurs authentifiés.
--}}
@auth
    <script type="module" src="{{ asset('js/offline/init.js') }}?v=1.3.5"></script>
@endauth
