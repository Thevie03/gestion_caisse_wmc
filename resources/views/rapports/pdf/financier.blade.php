<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport Financier Global</title>
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

        .boutique-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .boutique-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            flex: 1;
            min-width: 300px;
        }

        .boutique-title {
            font-size: 14px;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin-bottom: 10px;
            text-align: center;
        }

        .boutique-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .boutique-stat {
            text-align: center;
        }

        .boutique-stat-value {
            font-size: 14px;
            font-weight: bold;
        }

        .boutique-stat-label {
            font-size: 9px;
            color: #666;
        }

        .benefice {
            text-align: center;
            margin: 10px 0;
        }

        .benefice-value {
            font-size: 16px;
            font-weight: bold;
        }

        .benefice-positive {
            color: green;
        }

        .benefice-negative {
            color: red;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .summary-table th {
            background-color: {{ $primaryColor }};
            color: white;
            font-weight: bold;
        }

        .summary-table tr:nth-child(even) {
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
        <div class="report-title">RAPPORT FINANCIER GLOBAL</div>
        <div class="report-info">
            Période : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} -
            {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}<br>
            Généré le : {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($statsGlobales['chiffre_affaires_total'] ?? 0, 0, ',', ' ') }} FCFA
            </div>
            <div class="stat-label">CA Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($statsGlobales['depenses_totales'] ?? 0, 0, ',', ' ') }} FCFA</div>
            <div class="stat-label">Dépenses Totales</div>
        </div>
        <div class="stat-card">
            <div
                class="stat-value {{ ($statsGlobales['benefice_net'] ?? 0) >= 0 ? 'benefice-positive' : 'benefice-negative' }}">
                {{ number_format($statsGlobales['benefice_net'] ?? 0, 0, ',', ' ') }} FCFA
            </div>
            <div class="stat-label">Bénéfice Net</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($statsGlobales['nombre_transactions'] ?? 0) }}</div>
            <div class="stat-label">Transactions</div>
        </div>
    </div>

    <!-- Rapport par boutique -->
    <div class="section-title">RAPPORT PAR BOUTIQUE</div>
    <div class="boutique-cards">
        @foreach ($rapportBoutiques as $rapport)
            <div class="boutique-card">
                <div class="boutique-title">{{ $rapport['boutique']->nom }}</div>

                <div class="boutique-stats">
                    <div class="boutique-stat">
                        <div class="boutique-stat-value" style="color: green;">
                            {{ number_format($rapport['chiffre_affaires'], 0, ',', ' ') }} FCFA</div>
                        <div class="boutique-stat-label">Chiffre d'Affaires</div>
                    </div>
                    <div class="boutique-stat">
                        <div class="boutique-stat-value" style="color: orange;">
                            {{ number_format($rapport['depenses'], 0, ',', ' ') }} FCFA</div>
                        <div class="boutique-stat-label">Dépenses</div>
                    </div>
                </div>

                <div class="benefice">
                    <div
                        class="benefice-value {{ $rapport['benefice'] >= 0 ? 'benefice-positive' : 'benefice-negative' }}">
                        {{ number_format($rapport['benefice'], 0, ',', ' ') }} FCFA
                    </div>
                    <div class="boutique-stat-label">Bénéfice Net</div>
                </div>

                <div class="boutique-stats">
                    <div class="boutique-stat">
                        <div class="boutique-stat-value">{{ $rapport['nombre_ventes'] }}</div>
                        <div class="boutique-stat-label">Ventes</div>
                    </div>
                    <div class="boutique-stat">
                        <div class="boutique-stat-value">{{ $rapport['nombre_depenses'] }}</div>
                        <div class="boutique-stat-label">Dépenses</div>
                    </div>
                </div>

                @php
                    $marge =
                        $rapport['chiffre_affaires'] > 0
                            ? ($rapport['benefice'] / $rapport['chiffre_affaires']) * 100
                            : 0;
                @endphp
                <div style="text-align: center; margin-top: 10px;">
                    <small>Marge: <strong>{{ number_format($marge, 1) }}%</strong></small>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tableau de synthèse -->
    <div class="section-title">TABLEAU DE SYNTHÈSE</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Boutique</th>
                <th>Chiffre d'Affaires</th>
                <th>Dépenses</th>
                <th>Bénéfice</th>
                <th>Marge (%)</th>
                <th>Ventes</th>
                <th>Dépenses</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rapportBoutiques as $rapport)
                @php
                    $marge =
                        $rapport['chiffre_affaires'] > 0
                            ? ($rapport['benefice'] / $rapport['chiffre_affaires']) * 100
                            : 0;
                @endphp
                <tr>
                    <td><strong>{{ $rapport['boutique']->nom }}</strong></td>
                    <td style="text-align: right;">
                        <strong>{{ number_format($rapport['chiffre_affaires'], 0, ',', ' ') }} FCFA</strong>
                    </td>
                    <td style="text-align: right;"><strong>{{ number_format($rapport['depenses'], 0, ',', ' ') }}
                            FCFA</strong></td>
                    <td style="text-align: right; color: {{ $rapport['benefice'] >= 0 ? 'green' : 'red' }};">
                        <strong>{{ number_format($rapport['benefice'], 0, ',', ' ') }} FCFA</strong>
                    </td>
                    <td style="text-align: center;"><strong>{{ number_format($marge, 1) }}%</strong></td>
                    <td style="text-align: center;">{{ $rapport['nombre_ventes'] }}</td>
                    <td style="text-align: center;">{{ $rapport['nombre_depenses'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background-color: #f8f9fa; font-weight: bold;">
            <tr>
                <td><strong>TOTAL</strong></td>
                <td style="text-align: right;">
                    <strong>{{ number_format($statsGlobales['chiffre_affaires_total'], 0, ',', ' ') }} FCFA</strong>
                </td>
                <td style="text-align: right;">
                    <strong>{{ number_format($statsGlobales['depenses_totales'], 0, ',', ' ') }} FCFA</strong>
                </td>
                <td style="text-align: right; color: {{ $statsGlobales['benefice_net'] >= 0 ? 'green' : 'red' }};">
                    <strong>{{ number_format($statsGlobales['benefice_net'], 0, ',', ' ') }} FCFA</strong>
                </td>
                <td style="text-align: center;">
                    @php
                        $margeGlobale =
                            $statsGlobales['chiffre_affaires_total'] > 0
                                ? ($statsGlobales['benefice_net'] / $statsGlobales['chiffre_affaires_total']) * 100
                                : 0;
                    @endphp
                    <strong>{{ number_format($margeGlobale, 1) }}%</strong>
                </td>
                <td style="text-align: center;"><strong>{{ $statsGlobales['nombre_transactions'] }}</strong></td>
                <td style="text-align: center;">
                    <strong>{{ collect($rapportBoutiques)->sum('nombre_depenses') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Pied de page -->
    <div class="footer">
        <p><strong>Rapport généré automatiquement par GestionCaisse</strong></p>
        <p>Pour toute question, contactez-nous au +221 XX XX XX XX</p>
    </div>
</body>

</html>
