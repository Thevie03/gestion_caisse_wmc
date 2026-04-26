<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des Dépenses</title>
    <style>
        @php
            // Récupérer le thème de couleur - utiliser directement la variable passée depuis le contrôleur
            // Le contrôleur s'assure déjà de récupérer le bon theme_color
            if (!isset($themeColor) || empty($themeColor)) {
                // Si themeColor n'est pas défini, essayer depuis la boutique
                if (isset($boutique) && $boutique && isset($boutique->theme_color) && !empty($boutique->theme_color) && $boutique->theme_color !== 'default') {
                    $themeColor = $boutique->theme_color;
                } else {
                    $themeColor = '#007bff'; // Fallback final
                }
            }
            
            // S'assurer que themeColor est valide
            $themeColor = !empty($themeColor) && $themeColor !== 'default' ? $themeColor : '#007bff';
            
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

        .depenses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .depenses-table th,
        .depenses-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }

        .depenses-table th {
            background-color: {{ $primaryColor }};
            color: white;
            font-weight: bold;
        }

        .depenses-table tr:nth-child(even) {
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
        <div class="report-title">RAPPORT DES DÉPENSES</div>
        <div class="report-info">
            Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} -
            {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}<br>
            Généré le : {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Statistiques des dépenses -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_depenses']) }}</div>
            <div class="stat-label">Total Dépenses</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['montant_total'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Montant Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['moyenne_depense'], 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Moyenne par Dépense</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['depenses_par_categorie']->count() }}</div>
            <div class="stat-label">Catégories</div>
        </div>
    </div>

    <!-- Dépenses par catégorie -->
    <div class="section-title">DÉPENSES PAR CATÉGORIE</div>
    <table class="depenses-table">
        <thead>
            <tr>
                <th>Catégorie</th>
                <th>Nombre</th>
                <th>Montant</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stats['depenses_par_categorie'] as $categorie => $data)
                @php
                    $pourcentage = $stats['montant_total'] > 0 ? ($data['total'] / $stats['montant_total']) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $categorie }}</td>
                    <td style="text-align: center;">{{ $data['count'] }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($data['total'], 0, ',', ' ') }}
                            FCFA</strong></td>
                    <td style="text-align: center;">{{ number_format($pourcentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Évolution des dépenses par mois -->
    <div class="section-title">ÉVOLUTION DES DÉPENSES PAR MOIS</div>
    <table class="depenses-table">
        <thead>
            <tr>
                <th>Mois</th>
                <th>Montant</th>
                <th>Pourcentage</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maxMontant = $stats['depenses_par_mois']->max();
            @endphp
            @foreach ($stats['depenses_par_mois'] as $mois => $montant)
                @php
                    $pourcentage = $maxMontant > 0 ? ($montant / $maxMontant) * 100 : 0;
                    $moisFormate = \Carbon\Carbon::createFromFormat('Y-m', $mois)->format('M Y');
                @endphp
                <tr>
                    <td>{{ $moisFormate }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($montant, 0, ',', ' ') }} FCFA</strong>
                    </td>
                    <td style="text-align: center;">{{ number_format($pourcentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Détail des dépenses -->
    <div class="section-title">DÉTAIL DES DÉPENSES</div>
    <table class="depenses-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th>Montant</th>
                <th>Boutique</th>
                <th>Utilisateur</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($depenses as $depense)
                <tr>
                    <td>{{ $depense->date_depense->format('d/m/Y') }}</td>
                    <td>{{ $depense->description }}</td>
                    <td>{{ $depense->categorie }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($depense->montant, 0, ',', ' ') }}
                            FCFA</strong></td>
                    <td>{{ $depense->boutique->nom }}</td>
                    <td>{{ $depense->user->name }}</td>
                    <td>{{ $depense->notes ? Str::limit($depense->notes, 30) : '-' }}</td>
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




































