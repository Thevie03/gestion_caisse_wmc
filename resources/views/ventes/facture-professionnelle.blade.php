<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $vente->numero_vente }}</title>
    <style>
        @php // Récupérer le thème de couleur de la boutique
        $boutique =$vente->boutique;
        $themeColor =$boutique->theme_color ?? '#475569';

        // Fonction pour assombrir une couleur
        function darkenColor($color, $percent =20) {
            $color =ltrim($color, '#');

            if (strlen($color) !=6) {
                return '#334155'; // Fallback
            }

            $rgb =str_split($color, 2);
            $r =hexdec($rgb[0]);
            $g =hexdec($rgb[1]);
            $b =hexdec($rgb[2]);
            $r =max(0, min(255, $r - ($r * $percent / 100)));
            $g =max(0, min(255, $g - ($g * $percent / 100)));
            $b =max(0, min(255, $b - ($b * $percent / 100)));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }

        // Fonction pour éclaircir une couleur
        function lightenColor($color, $percent =20) {
            $color =ltrim($color, '#');

            if (strlen($color) !=6) {
                return '#64748b'; // Fallback
            }

            $rgb =str_split($color, 2);
            $r =hexdec($rgb[0]);
            $g =hexdec($rgb[1]);
            $b =hexdec($rgb[2]);
            $r =max(0, min(255, $r + ((255 - $r) * $percent / 100)));
            $g =max(0, min(255, $g + ((255 - $g) * $percent / 100)));
            $b =max(0, min(255, $b + ((255 - $b) * $percent / 100)));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }

        $primaryColor =$themeColor;
        $primaryDark =darkenColor($themeColor, 25);
        $primaryLight =lightenColor($themeColor, 30);
        $secondaryColor =lightenColor($themeColor, 50);
        @endphp

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Arial', sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #0f172a;
            background: white;
        }

        .facture {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
            background: white;
            /* Optimisation pour tenir sur une page si <= 10 produits */
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .facture-content {
            flex: 1;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid {{ $primaryColor }};
        }

        .logo-section {
            flex: 1;
        }

        .logo {
            margin-bottom: 8px;
        }

        .logo img {
            max-width: 180px;
            max-height: 80px;
            object-fit: contain;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 6px;
        }

        .boutique-info {
            font-size: 11px;
            color: #475569;
            line-height: 1.3;
        }

        .facture-details {
            text-align: right;
            flex: 1;
        }

        .facture-title {
            font-size: 20px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 8px;
        }

        .facture-info {
            font-size: 11px;
            color: #475569;
        }

        .client-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .client-info,
        .vendeur-info {
            flex: 1;
            padding: 10px;
            background: #f1f5f9;
            border-radius: 5px;
            margin: 0 8px;
        }

        .section-title {
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 6px;
            font-size: 12px;
        }

        .info-row {
            margin-bottom: 4px;
            font-size: 11px;
        }

        .label {
            font-weight: bold;
            color: #475569;
        }

        .produits-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .produits-table th {
            background: {{ $primaryColor }};
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }

        .produits-table td {
            padding: 6px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        .produits-table tr:nth-child(even) {
            background: #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totaux-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }

        .totaux-table {
            width: 280px;
            border-collapse: collapse;
        }

        .totaux-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }

        .totaux-table .label {
            font-weight: bold;
            text-align: right;
        }

        .total-final {
            background: {{ $primaryColor }};
            color: white;
            font-weight: bold;
            font-size: 12px;
        }

        .paiement-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .paiement-info,
        .notes-section {
            flex: 1;
            padding: 10px;
            background: #f1f5f9;
            border-radius: 5px;
            margin: 0 8px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            color: #475569;
            border-top: 2px solid {{ $primaryColor }};
            padding-top: 12px;
            margin-top: auto;
            position: relative;
        }

        .footer .merci {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 8px;
            padding: 0;
            background: transparent;
        }

        .footer>div {
            margin-bottom: 4px;
        }

        /* Optimisation pour tenir sur une page si <= 10 produits */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .facture {
                max-width: none;
                margin: 0;
                padding: 8px;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .facture-content {
                flex: 1;
            }

            .no-print {
                display: none !important;
            }

            /* Si plus de 10 produits, permettre le débordement */
            .facture.has-many-products {
                page-break-inside: auto;
            }

            /* Si <= 10 produits, forcer sur une page */
            .facture:not(.has-many-products) {
                page-break-inside: avoid;
                height: 100vh;
                overflow: hidden;
            }

            .facture:not(.has-many-products) .header {
                margin-bottom: 10px;
                padding-bottom: 8px;
            }

            .facture:not(.has-many-products) .client-section {
                margin-bottom: 10px;
            }

            .facture:not(.has-many-products) .produits-table {
                margin-bottom: 10px;
            }

            .facture:not(.has-many-products) .produits-table th,
            .facture:not(.has-many-products) .produits-table td {
                padding: 4px 5px;
                font-size: 10px;
            }

            .facture:not(.has-many-products) .totaux-section {
                margin-bottom: 10px;
            }

            .facture:not(.has-many-products) .paiement-section {
                margin-bottom: 10px;
            }

            .facture:not(.has-many-products) .footer {
                padding-top: 8px;
                margin-top: 10px;
            }

            .facture:not(.has-many-products) .footer .merci {
                font-size: 12px;
                margin-bottom: 6px;
            }
        }

        .print-buttons {
            text-align: center;
            margin-bottom: 15px;
            padding: 12px;
            background: #f1f5f9;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .print-buttons button {
            margin: 0 8px;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }

        .btn-print {
            background: {{ $primaryColor }};
            color: white;
        }

        .btn-close {
            background: {{ $secondaryColor }};
            color: white;
        }

        .btn-print:hover {
            background: {{ $primaryDark }};
        }

        .btn-close:hover {
            background: {{ $primaryLight }};
        }

        /* Alerte paiement partiel avec thème */
        .alert-warning {
            border: 2px solid #d97706 !important;
            background: #fff3cd !important;
        }

        .alert-warning h5 {
            color: #856404 !important;
        }
    </style>
</head>

<body>
    <div class="no-print print-buttons">
        <button class="btn-print" onclick="window.print()">
            🖨️ Imprimer la facture
        </button>
        <button class="btn-close" onclick="window.close()">
            ❌ Fermer
        </button>
    </div>

    <div class="facture {{ $vente->venteDetails->count() > 10 ? 'has-many-products' : '' }}">
        <div class="facture-content">
            <!-- En-tête -->
            <div class="header">
                <div class="logo-section">
                    @if ($vente->boutique->logo)
                        <div class="logo">
                            <img src="{{ $vente->boutique->logo }}" alt="{{ $vente->boutique->nom }}" />
                        </div>
                    @endif
                    <div class="company-name">{{ $vente->boutique->nom }}</div>
                    <div class="boutique-info">
                        {{ $vente->boutique->adresse }}<br>
                        Tél: {{ $vente->boutique->telephone }}<br>
                        @if ($vente->boutique->email)
                            Email: {{ $vente->boutique->email }}
                        @endif
                    </div>
                </div>
                <div class="facture-details">
                    <div class="facture-title">FACTURE</div>
                    <div class="facture-info">
                        <div><strong>N° Facture:</strong> {{ $vente->numero_vente }}</div>
                        <div><strong>Date:</strong> {{ $vente->created_at->format('d/m/Y') }}</div>
                        <div><strong>Heure:</strong> {{ $vente->created_at->format('H:i:s') }}</div>
                    </div>
                </div>
            </div>

            <!-- Informations client/vendeur -->
            <div class="client-section">
                <div class="vendeur-info">
                    <div class="section-title">Vendeur</div>
                    <div class="info-row">
                        <span class="label">Nom:</span> {{ $vente->user->name }}
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span> {{ $vente->user->email }}
                    </div>
                    <div class="info-row">
                        <span class="label">Téléphone:</span> {{ $vente->user->telephone ?? 'Non renseigné' }}
                    </div>
                </div>
                <div class="client-info">
                    @if ($vente->client)
                        <div class="section-title">Client</div>
                        <div class="info-row">
                            <span class="label">Nom:</span> {{ $vente->client->nom_complet }}
                        </div>
                        @if ($vente->client->telephone)
                            <div class="info-row">
                                <span class="label">Téléphone:</span> {{ $vente->client->telephone }}
                            </div>
                        @endif
                        @if ($vente->client->adresse)
                            <div class="info-row">
                                <span class="label">Adresse:</span> {{ $vente->client->adresse }}
                            </div>
                        @endif
                    @else
                        <div class="section-title">Boutique</div>
                        <div class="info-row">
                            <span class="label">Nom:</span> {{ $vente->boutique->nom }}
                        </div>
                        <div class="info-row">
                            <span class="label">Adresse:</span> {{ $vente->boutique->adresse }}
                        </div>
                        <div class="info-row">
                            <span class="label">Téléphone:</span> {{ $vente->boutique->telephone }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tableau des produits -->
            <table class="produits-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Produit</th>
                        <th style="width: 15%;" class="text-center">Quantité</th>
                        <th style="width: 20%;" class="text-right">Prix unitaire</th>
                        <th style="width: 25%;" class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vente->venteDetails as $detail)
                        <tr>
                            <td>
                                <strong>{{ $detail->produit->nom }}</strong><br>
                                <small style="color: #475569;">{{ $detail->produit->categorie }}</small>
                            </td>
                            <td class="text-center">{{ $detail->quantite }}</td>
                            <td class="text-right">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                            <td class="text-right"><strong>{{ number_format($detail->sous_total, 0, ',', ' ') }}
                                    FCFA</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totaux -->
            <div class="totaux-section">
                <table class="totaux-table">
                    <tr>
                        <td class="label">Sous-total:</td>
                        <td class="text-right">{{ number_format($vente->total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @if ($vente->remise > 0)
                        <tr>
                            <td class="label">Remise:</td>
                            <td class="text-right">-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endif
                    <tr class="total-final">
                        <td class="label">TOTAL:</td>
                        <td class="text-right">{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </table>
            </div>

            <!-- Section paiement partiel (si applicable) -->
            @if ($vente->statut_paiement == 'partiel' && $vente->solde_restant > 0)
                <div class="alert alert-warning"
                    style="margin: 12px 0; border: 2px solid #d97706; background: #fff3cd;">
                    <h5 style="text-align: center; color: #856404; margin-bottom: 10px; font-size: 13px;">
                        <i class="fas fa-exclamation-triangle"></i> PAIEMENT PARTIEL
                    </h5>
                    <table style="width: 100%; font-size: 12px;">
                        <tr>
                            <td style="padding: 4px;"><strong>Total de la facture:</strong></td>
                            <td style="padding: 4px; text-align: right;">
                                {{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.5);">
                            <td style="padding: 4px;"><strong>Montant payé:</strong></td>
                            <td style="padding: 4px; text-align: right;">
                                {{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px;"><strong>Solde restant:</strong></td>
                            <td style="padding: 4px; text-align: right; color: #dc2626; font-weight: bold;">
                                {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    </table>
                    <div
                        style="margin-top: 10px; padding-top: 10px; border-top: 2px solid #d97706; text-align: center; font-size: 13px; font-weight: bold; color: #dc2626;">
                        Montant à payer: {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA
                    </div>
                </div>
            @endif

            <!-- Informations de paiement et notes -->
            <div class="paiement-section">
                <div class="paiement-info">
                    <div class="section-title">Mode de paiement</div>
                    <div class="info-row">
                        <span class="label">Type:</span>
                        @switch($vente->mode_paiement)
                            @case('especes')
                                💰 Espèces
                            @break

                            @case('wave')
                                📱 Wave
                            @break

                            @case('orange_money')
                                📱 Orange Money
                            @break

                            @case('mtn_money')
                                📱 MTN Money
                            @break

                            @case('mobile_money')
                                📱 Mobile Money
                            @break

                            @case('carte')
                                💳 Carte bancaire
                            @break

                            @default
                                {{ ucfirst(str_replace('_', ' ', $vente->mode_paiement)) }}
                        @endswitch
                    </div>
                    <div class="info-row">
                        <span class="label">Montant payé:</span>
                        {{ number_format($vente->montant_paye ?? $vente->total_final, 0, ',', ' ') }} FCFA
                    </div>
                </div>

                @if ($vente->notes)
                    <div class="notes-section">
                        <div class="section-title">Notes</div>
                        <div class="info-row">{{ $vente->notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div class="merci">Merci pour votre confiance ! 🎉</div>
            <div><strong>GestionCaisse</strong> - Système de gestion de caisse professionnel</div>
            <div>Facture générée le {{ now()->format('d/m/Y à H:i:s') }}</div>
            <div>Conservez cette facture pour vos réclamations !</div>
            <div style="margin-top: 8px; font-size: 8px;">Protection de données personnelles</div>
        </div>
    </div>

    <!-- Script pour l'impression -->
    <script>
        // Impression automatique au chargement
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };

        // Gérer la fermeture après impression
        window.onafterprint = function() {
            // Optionnel : fermer la fenêtre après impression
            // window.close();
        };
    </script>
</body>

</html>
