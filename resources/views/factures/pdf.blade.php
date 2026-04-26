<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        @php
            // Récupérer le thème de couleur de la boutique
            $boutique = $boutique ?? $facture->vente->boutique;
            $themeColor = $themeColor ?? ($boutique->theme_color ?? '#475569');
            
            // Fonction pour assombrir une couleur
            function darkenColor($color, $percent = 20) {
                $color = ltrim($color, '#');
                if (strlen($color) != 6) {
                    return '#334155'; // Fallback
                }
                $rgb = str_split($color, 2);
                $r = hexdec($rgb[0]);
                $g = hexdec($rgb[1]);
                $b = hexdec($rgb[2]);
                $r = max(0, min(255, $r - ($r * $percent / 100)));
                $g = max(0, min(255, $g - ($g * $percent / 100)));
                $b = max(0, min(255, $b - ($b * $percent / 100)));
                return sprintf('#%02x%02x%02x', $r, $g, $b);
            }
            
            // Fonction pour convertir hex en RGB pour rgba
            function hexToRgb($color) {
                $color = ltrim($color, '#');
                if (strlen($color) != 6) {
                    return '71, 85, 105'; // Fallback
                }
                $rgb = str_split($color, 2);
                return hexdec($rgb[0]) . ', ' . hexdec($rgb[1]) . ', ' . hexdec($rgb[2]);
            }
            
            $primaryColor = $themeColor;
            $primaryDark = darkenColor($themeColor, 25);
            $accentColor = $themeColor;
            $primaryRgb = hexToRgb($themeColor);
        @endphp

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #0f172a;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid {{ $primaryColor }};
            padding-bottom: 20px;
        }

        .logo {
            margin-bottom: 10px;
        }

        .logo img {
            max-width: 200px;
            max-height: 100px;
            object-fit: contain;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 11px;
            color: #666;
        }

        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .invoice-details {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 5px;
        }

        .invoice-details h3 {
            margin: 0 0 10px 0;
            color: {{ $primaryColor }};
            font-size: 16px;
        }

        .invoice-details p {
            margin: 5px 0;
        }

        .client-info {
            background-color: #e2e8f0;
            padding: 15px;
            border-radius: 5px;
        }

        .client-info h3 {
            margin: 0 0 10px 0;
            color: {{ $primaryColor }};
            font-size: 16px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .products-table th,
        .products-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .products-table th {
            background-color: {{ $primaryColor }};
            color: white;
            font-weight: bold;
        }

        .products-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px 0;
        }

        .total-line.final {
            border-top: 2px solid {{ $primaryColor }};
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
            padding-top: 10px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .payment-info {
            background-color: rgba({{ $primaryRgb }}, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .boutique-info {
            background-color: rgba({{ $primaryRgb }}, 0.05);
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <!-- En-tête de la facture -->
    <div class="header">
        @if ($facture->vente->boutique->logo)
            <div class="logo">
                <img src="{{ $facture->vente->boutique->logo }}" alt="{{ $facture->vente->boutique->nom }}" />
            </div>
        @endif
        <div class="company-name">{{ $facture->vente->boutique->nom }}</div>
        <div class="company-info">
            {{ $facture->vente->boutique->adresse }}<br>
            Téléphone: {{ $facture->vente->boutique->telephone }}
            @if ($facture->vente->boutique->email)
                | Email: {{ $facture->vente->boutique->email }}
            @endif
        </div>
    </div>

    <!-- Informations de la facture -->
    <div class="invoice-info">
        <div class="invoice-details">
            <h3>FACTURE</h3>
            <p><strong>N° Facture:</strong> {{ $facture->numero_facture }}</p>
            <p><strong>Date:</strong> {{ $facture->created_at->format('d/m/Y à H:i') }}</p>
            <p><strong>Vendeur:</strong> {{ $facture->user->name }}</p>
        </div>

        <div class="client-info">
            <h3>CLIENT</h3>
            <p><strong>Nom:</strong> {{ $facture->nom_client ?? 'Client anonyme' }}</p>
            @if ($facture->telephone_client)
                <p><strong>Téléphone:</strong> {{ $facture->telephone_client }}</p>
            @endif
            @if ($facture->email_client)
                <p><strong>Email:</strong> {{ $facture->email_client }}</p>
            @endif
        </div>
    </div>

    <!-- Informations boutique avec logo -->
    <div class="boutique-info">
        <div class="d-flex align-items-center mb-2">
            @if (isset($logo) && $logo)
                <img src="{{ $logo }}" alt="Logo" height="40" class="me-3">
            @endif
            <div>
                <strong>Boutique:</strong> {{ $facture->boutique->nom }}<br>
                <strong>Description:</strong> {{ $facture->boutique->description ?? 'Boutique de vente' }}
            </div>
        </div>
    </div>

    <!-- Tableau des produits -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Produit</th>
                <th style="width: 15%;">Catégorie</th>
                <th style="width: 10%;">Qté</th>
                <th style="width: 15%;">Prix unitaire</th>
                <th style="width: 10%;">Remise</th>
                <th style="width: 15%;">Sous-total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($facture->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->produit->nom }}</strong>
                        @if ($detail->produit->description)
                            <br><small>{{ $detail->produit->description }}</small>
                        @endif
                    </td>
                    <td>{{ $detail->produit->categorie }}</td>
                    <td style="text-align: center;">{{ $detail->quantite }}</td>
                    <td style="text-align: right;">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: right;">
                        @if ($detail->remise > 0)
                            -{{ number_format($detail->remise, 0, ',', ' ') }} FCFA
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: right;"><strong>{{ number_format($detail->sous_total, 0, ',', ' ') }}
                            FCFA</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Résumé financier -->
    <div class="total-section">
        <div class="total-line">
            <span>Sous-total:</span>
            <span>{{ number_format($facture->details->sum('sous_total'), 0, ',', ' ') }} FCFA</span>
        </div>

        @if ($facture->remise_globale > 0)
            <div class="total-line">
                <span>Remise globale:</span>
                <span>-{{ number_format($facture->remise_globale, 0, ',', ' ') }} FCFA</span>
            </div>
        @endif

        <div class="total-line final">
            <span>TOTAL:</span>
            <span>{{ number_format($facture->total, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    <!-- Section paiement partiel -->
    @if (isset($facture->vente) && $facture->vente->statut_paiement == 'partiel' && $facture->vente->solde_restant > 0)
        <div style="background: #fff3cd; border: 2px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h5 style="text-align: center; color: #856404; margin-bottom: 15px;">
                ⚠️ PAIEMENT PARTIEL
            </h5>
            <table style="width: 100%; font-size: 13px;">
                <tr>
                    <td style="padding: 5px;"><strong>Total facture:</strong></td>
                    <td style="padding: 5px; text-align: right;">
                        {{ number_format($facture->vente->total_final, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="background: rgba(255,255,255,0.5);">
                    <td style="padding: 5px;"><strong>Montant payé:</strong></td>
                    <td style="padding: 5px; text-align: right;">
                        {{ number_format($facture->vente->montant_paye, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong>Solde restant:</strong></td>
                    <td style="padding: 5px; text-align: right; color: #dc2626; font-weight: bold;">
                        {{ number_format($facture->vente->solde_restant, 0, ',', ' ') }} FCFA
                    </td>
                </tr>
            </table>
            <div
                style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #ffc107; text-align: center; font-size: 15px; font-weight: bold; color: #dc2626;">
                Montant à payer: {{ number_format($facture->vente->solde_restant, 0, ',', ' ') }} FCFA
            </div>
        </div>
    @endif

    <!-- Informations de paiement -->
    <div class="payment-info">
        <strong>Mode de paiement:</strong>
        @php
            $modePaiement = $facture->vente->mode_paiement ?? $facture->mode_paiement ?? null;
            $modeText = match ($modePaiement) {
                'especes' => 'Espèces',
                'wave' => 'Wave',
                'orange_money' => 'Orange Money',
                'mtn_money' => 'MTN Money',
                'mobile_money' => 'Mobile Money',
                'carte' => 'Carte bancaire',
                default => $modePaiement ? ucfirst(str_replace('_', ' ', $modePaiement)) : 'Non renseigné',
            };
        @endphp
        {{ $modeText }}
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>Merci pour votre achat !</strong></p>
        <p>Cette facture a été générée automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
        <p>Pour toute réclamation, contactez-nous au +221 XX XX XX XX</p>
        <p style="margin-top: 10px; font-size: 9px;">Protection de données personnelles</p>
    </div>
</body>

</html>
