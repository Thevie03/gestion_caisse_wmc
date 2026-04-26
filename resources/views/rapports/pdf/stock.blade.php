<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport du Stock</title>
    <style>
        @php
            $themeColor = $themeColor ?? '#007bff';
            function darkenColor($color, $percent = 20) {
                $color = ltrim($color, '#');
                if (strlen($color) != 6) return '#0056b3';
                $rgb = str_split($color, 2);
                $r = max(0, min(255, hexdec($rgb[0]) - (hexdec($rgb[0]) * $percent / 100)));
                $g = max(0, min(255, hexdec($rgb[1]) - (hexdec($rgb[1]) * $percent / 100)));
                $b = max(0, min(255, hexdec($rgb[2]) - (hexdec($rgb[2]) * $percent / 100)));
                return sprintf('#%02x%02x%02x', $r, $g, $b);
            }
            $primaryColor = $themeColor;
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

        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .stock-table th,
        .stock-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .stock-table th {
            background-color: {{ $primaryColor }};
            color: white;
            font-weight: bold;
        }

        .stock-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
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
        <div class="report-title">RAPPORT DU STOCK</div>
        <div class="report-info">
            Généré le : {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Statistiques du stock -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_produits']) }}</div>
            <div class="stat-label">Total produits/articles</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Valeur du Stock</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['produits_faibles']) }}</div>
            <div class="stat-label">Stock Faible</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['produits_rupture']) }}</div>
            <div class="stat-label">Rupture de Stock</div>
        </div>
    </div>

    <!-- Stock par catégorie -->
    <div class="section-title">STOCK PAR CATÉGORIE</div>
    <table class="stock-table">
        <thead>
            <tr>
                <th>Catégorie</th>
                <th>Quantité</th>
                <th>Valeur</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stats['categories'] as $categorie => $data)
                @php
                    $pourcentage = $stats['valeur_stock'] > 0 ? ($data['valeur'] / $stats['valeur_stock']) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $categorie }}</td>
                    <td style="text-align: center;">{{ $data['quantite'] }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($data['valeur'], 0, ',', ' ') }}
                            FCFA</strong></td>
                    <td style="text-align: center;">{{ number_format($pourcentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Alertes de stock -->
    @php
        $produitsFaibles = $produits->where('quantite_stock', '<=', 'stock_minimum');
        $produitsRupture = $produits->where('quantite_stock', 0);
    @endphp

    @if ($produitsFaibles->count() > 0)
        <div class="section-title">ALERTES - PRODUITS EN STOCK FAIBLE</div>
        <div class="alert-box">
            @foreach ($produitsFaibles->take(10) as $produit)
                <p><strong>{{ $produit->nom }}</strong> - Stock: {{ $produit->quantite_stock }} (Minimum:
                    {{ $produit->stock_minimum }})</p>
            @endforeach
            @if ($produitsFaibles->count() > 10)
                <p><em>... et {{ $produitsFaibles->count() - 10 }} autres produits</em></p>
            @endif
        </div>
    @endif

    @if ($produitsRupture->count() > 0)
        <div class="section-title">ALERTES - PRODUITS EN RUPTURE</div>
        <div class="alert-box alert-danger">
            @foreach ($produitsRupture->take(10) as $produit)
                <p><strong>{{ $produit->nom }}</strong> - Stock: 0</p>
            @endforeach
            @if ($produitsRupture->count() > 10)
                <p><em>... et {{ $produitsRupture->count() - 10 }} autres produits</em></p>
            @endif
        </div>
    @endif

    <!-- Détail du stock -->
    <div class="section-title">DÉTAIL DU STOCK</div>
    <table class="stock-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Catégorie</th>
                <th>Boutique</th>
                <th>Stock</th>
                <th>Minimum</th>
                <th>Prix Achat</th>
                <th>Prix Vente</th>
                <th>Valeur</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produits as $produit)
                <tr>
                    <td>{{ $produit->nom }}</td>
                    <td>{{ $produit->categorie }}</td>
                    <td>{{ $produit->boutique->nom }}</td>
                    <td style="text-align: center;">{{ $produit->quantite_stock }}</td>
                    <td style="text-align: center;">{{ $produit->stock_minimum }}</td>
                    <td style="text-align: right;">{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: right;">{{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: right;">
                        <strong>{{ number_format($produit->quantite_stock * $produit->prix_achat, 0, ',', ' ') }}
                            FCFA</strong>
                    </td>
                    <td style="text-align: center;">
                        @if ($produit->quantite_stock == 0)
                            <strong style="color: red;">RUPTURE</strong>
                        @elseif($produit->quantite_stock <= $produit->stock_minimum)
                            <strong style="color: orange;">FAIBLE</strong>
                        @else
                            <strong style="color: green;">NORMAL</strong>
                        @endif
                    </td>
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
