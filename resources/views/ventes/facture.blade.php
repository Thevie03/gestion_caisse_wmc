<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $vente->numero_vente }}</title>
    <!--
        TICKET CAISSE OPTIMISÉ POUR IMPRIMANTES THERMIQUES
        Format adaptatif : 58mm (POS) ou 80mm (standard)
        Compatible avec : XPRINTER XP Q200, Epson TM-T20, Star TSP, Bixolon SRP, etc.
        Largeur papier : {{ $ticketWidth ?? 80 }}mm
    -->
    @php
        $width = $ticketWidth ?? 80;
        $is58mm = $width == 58;
        $fontSize = $is58mm ? 9 : 10;
        $fontSizeSmall = $is58mm ? 8 : 9;
        $fontSizeLarge = $is58mm ? 14 : 15;
        $logoMaxWidth = $is58mm ? 45 : 60;
        $logoMaxHeight = $is58mm ? 22 : 30;
        $padding = $is58mm ? '1.5mm 2mm' : '1.5mm 2mm';
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Prévenir les coupures de texte */
        * {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        /* S'assurer que tout reste dans les limites */
        html {
            width: 100%;
        }

        body {
            font-family: 'Arial', 'DejaVu Sans', sans-serif;
            font-size: {{ $fontSize }}px;
            line-height: 1.4;
            color: #000;
            background: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .facture {
            width: min({{ $width }}mm, 100%);
            max-width: 100%;
            min-width: 0;
            margin: 0 auto;
            padding: {{ $padding }};
            background: white;
            box-sizing: border-box;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            overflow: visible;
            position: relative;
        }

        @media print {
            body {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                display: block !important;
                text-align: left !important;
            }

            .print-buttons {
                display: none !important;
            }

            .facture {
                /* Taille réelle en mm pour l'impression */
                width: {{ $width }}mm !important;
                max-width: {{ $width }}mm !important;
                min-width: {{ $width }}mm !important;
                padding: {{ $padding }} !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                font-size: {{ $fontSize }}px !important;
            }
        }

        .header {
            text-align: center;
            border-bottom: 4px solid #000;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .logo {
            margin-bottom: 8px;
        }

        .logo img {
            max-width: {{ $logoMaxWidth }}mm;
            max-height: {{ $logoMaxHeight }}mm;
            object-fit: contain;
        }

        .company-name {
            font-size: {{ $fontSizeLarge }}px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            line-height: 1.2;
            max-width: 100%;
        }

        .boutique-info {
            font-size: {{ $fontSizeSmall }}px;
            color: #000;
            line-height: 1.3;
            font-weight: 600;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            max-width: 100%;
        }

        .facture-info {
            display: block;
            margin-bottom: {{ $is58mm ? '10px' : '12px' }};
            font-size: {{ $fontSizeSmall }}px;
            font-weight: 700;
            width: 100%;
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .facture-info>div {
            margin-bottom: 3px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            max-width: 100%;
        }

        .facture-info .label {
            font-weight: bold;
        }

        .client-info {
            margin-bottom: {{ $is58mm ? '10px' : '12px' }};
            font-size: {{ $fontSizeSmall }}px;
            font-weight: 600;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            max-width: 100%;
        }

        .client-info>div {
            margin-bottom: 2px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            max-width: 100%;
        }

        .client-info .label {
            font-weight: bold;
        }

        .produits {
            margin-bottom: 10px;
        }

        .produit-row {
            display: block;
            width: 100%;
            margin-bottom: {{ $is58mm ? '4px' : '5px' }};
            font-size: {{ $fontSizeSmall }}px;
            line-height: 1.4;
            font-weight: 500;
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .produit-ligne {
            display: flex;
            align-items: flex-start;
            width: 100%;
            margin-bottom: 2px;
        }

        .produit-nom {
            flex: 1 1 auto;
            min-width: 0;
            padding-right: {{ $is58mm ? '2px' : '3px' }};
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            white-space: normal;
            line-height: 1.3;
            font-size: {{ $fontSizeSmall }}px;
        }

        .produit-quantite {
            flex: 0 0 {{ $is58mm ? '7mm' : '8mm' }};
            text-align: center;
            font-weight: 700;
            white-space: nowrap;
            font-size: {{ $fontSizeSmall }}px;
        }

        .produit-prix {
            flex: 0 0 {{ $is58mm ? '12mm' : '14mm' }};
            text-align: right;
            font-weight: 600;
            white-space: nowrap;
            padding-left: 2px;
            font-size: {{ $fontSizeSmall }}px;
        }

        .produit-total {
            flex: 0 0 {{ $is58mm ? '14mm' : '16mm' }};
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
            padding-left: 2px;
            font-size: {{ $fontSizeSmall }}px;
        }

        .separator {
            border-top: 2px solid #000;
            margin: 12px 0;
        }

        .totaux {
            margin-bottom: 10px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: {{ $is58mm ? '4px' : '5px' }};
            font-size: {{ $is58mm ? '10px' : '12px' }}px;
            font-weight: 600;
            width: 100%;
            max-width: 100%;
        }

        .total-row span:first-child {
            flex: 1 1 auto;
            min-width: 0;
        }

        .total-row span:last-child {
            flex: 0 0 auto;
            white-space: nowrap;
            padding-left: 4px;
            text-align: right;
        }

        .total-final {
            font-weight: bold;
            font-size: {{ $is58mm ? '13px' : '16px' }}px;
            border-top: 3px solid #000;
            padding-top: 8px;
            margin-top: 8px;
            letter-spacing: 1px;
        }

        .paiement {
            margin-bottom: {{ $is58mm ? '10px' : '14px' }};
            font-size: {{ $is58mm ? '10px' : '12px' }}px;
            font-weight: 700;
        }

        .paiement .label {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: {{ $is58mm ? '7px' : '9px' }}px;
            color: #000;
            border-top: 2px solid #000;
            padding-top: {{ $is58mm ? '8px' : '12px' }};
            margin-top: {{ $is58mm ? '10px' : '14px' }};
            line-height: 1.5;
            font-weight: 600;
        }

        .merci {
            font-size: {{ $is58mm ? '11px' : '14px' }}px;
            font-weight: bold;
            text-align: center;
            margin: 14px 0;
            letter-spacing: 1px;
        }

        @media print {
            @page {
                size: {{ $width }}mm auto;
                margin: 0 auto;
                padding: 0;
            }

            html,
            body {
                width: 100%;
                margin: 0 auto;
                padding: 0;
                display: flex;
                justify-content: center;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                display: flex !important;
                justify-content: center !important;
                align-items: flex-start !important;
            }

            .facture {
                width: {{ $width }}mm !important;
                max-width: {{ $width }}mm !important;
                min-width: {{ $width }}mm !important;
                margin: 0 auto !important;
                padding: {{ $padding }} !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
                overflow: hidden !important;
            }

            * {
                max-width: 100% !important;
                box-sizing: border-box !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            p,
            div,
            span,
            td,
            th {
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                word-break: break-word !important;
            }

            .produit-row {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .produit-ligne {
                display: flex !important;
                width: 100% !important;
                max-width: 100% !important;
                flex-wrap: nowrap !important;
            }

            .produit-nom {
                flex: 1 1 auto !important;
                min-width: 0 !important;
                max-width: none !important;
            }

            .produit-quantite,
            .produit-prix,
            .produit-total {
                flex-shrink: 0 !important;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-buttons {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .print-buttons button {
            margin: 0 5px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-print {
            background: #dc2626;
            color: white;
        }

        .btn-close {
            background: #6c757d;
            color: white;
        }
    </style>
</head>

<body>
    <div class="no-print print-buttons">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Fermer
        </button>
    </div>

    <div class="facture">
        <!-- En-tête -->
        <div class="header">
            @if ($vente->boutique->logo)
                <div class="logo">
                    <img src="{{ $vente->boutique->logo }}" alt="{{ $vente->boutique->nom }}" />
                </div>
            @endif
            <div class="company-name">{{ $vente->boutique->nom }}</div>
            <div class="boutique-info">
                {{ $vente->boutique->adresse }}<br>
                Tel: {{ $vente->boutique->telephone }}
                @if ($vente->boutique->email)
                    <br>Email: {{ $vente->boutique->email }}
                @endif
            </div>
        </div>

        <!-- Informations de la facture -->
        <div class="facture-info">
            <div><span class="label">Facture:</span> {{ $vente->numero_vente }}</div>
            <div><span class="label">Date:</span> {{ $vente->created_at->format('d/m/Y H:i') }}</div>
        </div>

        <!-- Informations client et vendeur -->
        <div class="client-info">
            @if ($vente->client)
                <div><span class="label">Client:</span> {{ $vente->client->nom_complet }}</div>
                @if ($vente->client->telephone)
                    <div><span class="label">Tél:</span> {{ $vente->client->telephone }}</div>
                @endif
            @else
                <div><span class="label">Vente anonyme</span></div>
            @endif
            <div><span class="label">Vendeur:</span> {{ $vente->user->name }}</div>
        </div>

        <!-- Produits -->
        <div class="produits">
            <div class="produit-ligne"
                style="font-weight: bold; border-bottom: 3px solid #000; padding-bottom: {{ $is58mm ? '3px' : '4px' }}; margin-bottom: {{ $is58mm ? '3px' : '4px' }}; font-size: {{ $fontSizeSmall }}px;">
                <div class="produit-nom" style="white-space: normal;">Produit</div>
                <div class="produit-quantite">Qté</div>
                <div class="produit-prix">Prix</div>
                <div class="produit-total">Total</div>
            </div>

            @foreach ($vente->venteDetails as $detail)
                <div class="produit-ligne">
                    <div class="produit-nom">{{ $detail->produit->nom }}</div>
                    <div class="produit-quantite">{{ $detail->quantite }}</div>
                    <div class="produit-prix">{{ number_format($detail->prix_unitaire, 0, ',', ' ') }}</div>
                    <div class="produit-total">{{ number_format($detail->sous_total, 0, ',', ' ') }}</div>
                </div>
            @endforeach
        </div>

        <div class="separator"></div>

        <!-- Totaux -->
        <div class="totaux">
            <div class="total-row">
                <span>Sous-total:</span>
                <span>{{ number_format($vente->total, 0, ',', ' ') }} FCFA</span>
            </div>

            @if ($vente->remise > 0)
                <div class="total-row">
                    <span>Remise:</span>
                    <span>-{{ number_format($vente->remise, 0, ',', ' ') }} FCFA</span>
                </div>
            @endif

            <div class="total-row total-final">
                <span>TOTAL:</span>
                <span>{{ number_format($vente->total_final, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        @if ($vente->statut_paiement == 'partiel' && $vente->solde_restant > 0)
            <!-- Section paiement partiel -->
            <div class="paiement-partiel"
                style="background: #fff3cd; border: 2px solid #ffc107; padding: 8px; margin: 10px 0; border-radius: 3px;">
                <div
                    style="text-align: center; font-weight: bold; color: #856404; margin-bottom: 6px; font-size: 13px;">
                    ⚠️ PAIEMENT PARTIEL
                </div>
                <div style="font-size: 11px; line-height: 1.5; font-weight: 600;">
                    <div style="margin-bottom: 5px;">
                        <strong>Payé:</strong> {{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA
                    </div>
                    <div style="margin-bottom: 5px;">
                        <strong>Reste:</strong> <span style="color: #dc2626; font-size: 13px; font-weight: bold;">
                            {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div
                        style="margin-top: 10px; padding-top: 6px; border-top: 3px solid #ffc107; text-align: center; font-weight: bold; color: #dc2626; font-size: 12px;">
                        A payer: {{ number_format($vente->solde_restant, 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>
        @endif

        <!-- Mode de paiement -->
        <div class="paiement">
            <div><span class="label">Mode de paiement:</span>
                @switch($vente->mode_paiement)
                    @case('especes')
                        Espèces
                    @break

                    @case('wave')
                        Wave
                    @break

                    @case('orange_money')
                        Orange Money
                    @break

                    @case('mtn_money')
                        MTN Money
                    @break

                    @case('mobile_money')
                        Mobile Money
                    @break

                    @case('carte')
                        Carte bancaire
                    @break

                    @default
                        {{ ucfirst(str_replace('_', ' ', $vente->mode_paiement)) }}
                @endswitch
            </div>
        </div>

        @if ($vente->notes)
            <div class="notes"
                style="margin-bottom: {{ $is58mm ? '10px' : '15px' }}; font-size: {{ $fontSize }}px; font-weight: 600;">
                <div><span class="label">Notes:</span> {{ $vente->notes }}</div>
            </div>
        @endif

        <!-- Message de remerciement -->
        <div class="merci">
            Merci pour votre achat !
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <div>GestionCaisse - Système de gestion de caisse</div>
            <div>Facture générée le {{ now()->format('d/m/Y à H:i:s') }}</div>
            <div>Pour toute réclamation, conserver cette facture</div>
            <div style="margin-top: 8px; font-size: 8px;">Protection de données personnelles</div>
        </div>
    </div>

    <!-- Script pour l'impression automatique -->
    <script>
        // Impression automatique au chargement de la page
        window.onload = function() {
            // Attendre un peu pour que la page se charge complètement
            setTimeout(function() {
                window.print();
            }, 500);
        };

        // Gérer la fermeture après impression
        window.onafterprint = function() {
            // Optionnel : fermer la fenêtre après impression
            // window.close();
        };
    </script>
</body>

</html>
