<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Ventes</title>
    <style>
        @php
            // Récupérer le thème de couleur
            $themeColor = $themeColor ?? '#007bff';
            
            // Fonction pour assombrir une couleur
            function darkenColor($color, $percent = 20) {
                $color = ltrim($color, '#');
                if (strlen($color) != 6) {
                    return '#0056b3'; // Fallback
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
            
            $primaryColor = $themeColor;
            $primaryDark = darkenColor($themeColor, 25);
        @endphp
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid {{ $primaryColor }};
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }

        .report-info {
            font-size: 11px;
            color: #666;
        }

        .stats-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            flex: 1;
            margin: 0 5px;
        }

        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: {{ $primaryColor }};
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin: 20px 0 10px 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
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

        .ventes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .ventes-table th,
        .ventes-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .ventes-table th {
            background-color: {{ $primaryColor }};
            color: white;
            font-weight: bold;
        }

        .ventes-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <!-- En-tête du rapport -->
    <div class="header">
        <div class="company-name">{{ isset($boutique) && $boutique ? $boutique->nom : 'GESTION CAISSE' }}</div>
        <div class="report-title">RAPPORT DES VENTES</div>
        <div class="report-info">
            Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} -
            {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}<br>
            Type : {{ ucfirst($typeRapport) }}<br>
            Généré le : {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Statistiques du rapport -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_ventes']) }}</div>
            <div class="stat-label">Total Ventes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['chiffre_affaires'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Chiffre d'Affaires</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['moyenne_vente'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Moyenne par Vente</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['meilleur_jour'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Meilleur Jour</div>
        </div>
    </div>

    <!-- Top 10 des produits vendus -->
    <div class="section-title">TOP 10 DES PRODUITS VENDUS</div>
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Produit</th>
                <th style="width: 15%;">Quantité</th>
                <th style="width: 30%;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @if ($stats['produits_vendus'] && $stats['produits_vendus']->count() > 0)
                @foreach ($stats['produits_vendus']->take(10) as $produit)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $produit['nom'] ?? 'N/A' }}</td>
                        <td style="text-align: center;">{{ $produit['quantite'] ?? 0 }}</td>
                        <td style="text-align: right;">
                            <strong>{{ number_format($produit['montant'] ?? 0, 0, ',', ' ') }}
                                FCFA</strong>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">Aucun produit vendu pour cette période
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Ventes par mode de paiement -->
    <div class="section-title">VENTES PAR MODE DE PAIEMENT</div>
    <table class="products-table">
        <thead>
            <tr>
                <th>Mode de Paiement</th>
                <th>Nombre</th>
                <th>Montant</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @if ($stats['ventes_par_mode_paiement'] && $stats['ventes_par_mode_paiement']->count() > 0)
                @foreach ($stats['ventes_par_mode_paiement'] as $mode => $data)
                    @php
                        $pourcentage =
                            $stats['chiffre_affaires'] > 0 ? ($data['total'] / $stats['chiffre_affaires']) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $mode)) }}</td>
                        <td style="text-align: center;">{{ $data['count'] ?? 0 }}</td>
                        <td style="text-align: right;"><strong>{{ number_format($data['total'] ?? 0, 0, ',', ' ') }}
                                FCFA</strong></td>
                        <td style="text-align: center;">{{ number_format($pourcentage, 1) }}%</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">Aucune donnée disponible</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Détail des ventes -->
    <div class="section-title">DÉTAIL DES VENTES</div>
    <table class="ventes-table">
        <thead>
            <tr>
                <th>N° Facture</th>
                <th>Date</th>
                <th>Client</th>
                <th>Boutique</th>
                <th>Vendeur</th>
                <th>Total</th>
                <th>Paiement</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventes as $vente)
                <tr>
                    <td>{{ $vente->numero_vente }}</td>
                    <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $vente->client ? $vente->client->nom_complet : 'Client anonyme' }}</td>
                    <td>{{ optional($vente->boutique)->nom ?? 'N/A' }}</td>
                    <td>{{ optional($vente->user)->name ?? 'N/A' }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($vente->total_final, 0, ',', ' ') }}
                            FCFA</strong></td>
                    <td>{{ ucfirst(str_replace('_', ' ', $vente->mode_paiement)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>Rapport généré automatiquement par GestionCaisse</strong></p>
        <p>Pour toute question, contactez-nous au +221 XX XX XX XX</p>
    </div>
</body>

</html>
