<table role="presentation"
    style="width: 100%; border-collapse: collapse; background-color: #f5f5f5; padding: 20px; font-family: Arial, sans-serif;">
    <tr>
        <td align="center">
            <table role="presentation"
                style="max-width: 600px; width: 100%; background-color: #ffffff; border-collapse: collapse; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <!-- Header -->
                <tr>
                    <td style="padding: 30px 30px 20px 30px; text-align: center; border-bottom: 2px solid #e5e7eb;">
                        @php
                            // S'assurer que l'URL du logo est absolue pour les emails
                            $logoUrl = $boutique->logo ?? null;
                            if ($logoUrl) {
                                // Si l'URL ne commence pas par http, la convertir en URL absolue
    if (!str_starts_with($logoUrl, 'http://') && !str_starts_with($logoUrl, 'https://')) {
                                    $logoUrl = url($logoUrl);
                                }
                            }
                        @endphp
                        @if ($logoUrl)
                            <div style="margin-bottom: 15px;">
                                <img src="{{ $logoUrl }}" alt="{{ $boutique->nom ?? config('app.name') }}"
                                    style="max-width: 200px; max-height: 100px; height: auto; width: auto; display: block; margin: 0 auto; border: 0;">
                            </div>
                        @endif
                        <h1
                            style="margin: {{ $logoUrl ? '15px 0 0 0' : '0' }}; font-size: 24px; color: #1f2937; font-weight: bold;">
                            {{ $boutique->nom ?? config('app.name') }}
                        </h1>
                    </td>
                </tr>

                <!-- Greeting -->
                <tr>
                    <td style="padding: 30px 30px 10px 30px;">
                        <p style="margin: 0; font-size: 16px; color: #333333; line-height: 1.6;">
                            Bonjour {{ $client ? $client->nom_complet : 'Cher client' }},
                        </p>
                    </td>
                </tr>

                <!-- Introduction -->
                <tr>
                    <td style="padding: 10px 30px 20px 30px;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
                            Nous vous remercions pour votre achat chez <strong
                                style="color: #1f2937;">{{ $boutique->nom ?? config('app.name') }}</strong>.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 0 30px 30px 30px;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
                            Votre facture <strong style="color: #1f2937;">{{ $facture->numero_facture }}</strong> est
                            disponible en pièce jointe.
                        </p>
                    </td>
                </tr>

                <!-- Order Summary -->
                <tr>
                    <td style="padding: 0 30px 20px 30px;">
                        <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold; color: #1f2937;">
                            Résumé de votre commande
                        </h2>

                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 8px 0; font-size: 14px; width: 180px; color: #4b5563;">
                                    <strong style="color: #1f2937;">Numéro de facture :</strong>
                                </td>
                                <td style="padding: 8px 0; font-size: 14px; color: #1f2937;">
                                    {{ $facture->numero_facture }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; font-size: 14px; color: #4b5563;">
                                    <strong style="color: #1f2937;">Date :</strong>
                                </td>
                                <td style="padding: 8px 0; font-size: 14px; color: #1f2937;">
                                    {{ $facture->created_at->format('d/m/Y à H:i') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; font-size: 14px; color: #4b5563;">
                                    <strong style="color: #1f2937;">Total :</strong>
                                </td>
                                <td style="padding: 8px 0; font-size: 14px; font-weight: bold; color: #1f2937;">
                                    {{ number_format($facture->total, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                            @if ($facture->remise_globale > 0)
                                <tr>
                                    <td style="padding: 8px 0; font-size: 14px; color: #4b5563;">
                                        <strong style="color: #1f2937;">Remise :</strong>
                                    </td>
                                    <td style="padding: 8px 0; font-size: 14px; color: #dc2626;">
                                        -{{ number_format($facture->remise_globale, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-size: 14px; color: #4b5563;">
                                        <strong style="color: #1f2937;">Total final :</strong>
                                    </td>
                                    <td style="padding: 8px 0; font-size: 14px; font-weight: bold; color: #1f2937;">
                                        {{ number_format($facture->total - $facture->remise_globale, 0, ',', ' ') }}
                                        FCFA
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td style="padding: 8px 0; font-size: 14px; color: #4b5563;">
                                    <strong style="color: #1f2937;">Mode de paiement :</strong>
                                </td>
                                <td style="padding: 8px 0; font-size: 14px; color: #1f2937;">
                                    {{ ucfirst(str_replace('_', ' ', $facture->mode_paiement)) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Products -->
                <tr>
                    <td style="padding: 0 30px 20px 30px;">
                        <h2 style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold; color: #1f2937;">
                            Produits achetés
                        </h2>

                        <table role="presentation" style="width: 100%; border-collapse: collapse;">
                            @foreach ($facture->vente->details as $detail)
                                <tr>
                                    <td
                                        style="padding: 10px 0; font-size: 14px; border-bottom: 1px solid #e5e7eb; color: #1f2937;">
                                        <strong>{{ $detail->produit->nom }}</strong> (x{{ $detail->quantite }}) -
                                        {{ number_format($detail->sous_total, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                <!-- Contact Information -->
                @if ($boutique->adresse || $boutique->telephone || $boutique->email)
                    <tr>
                        <td style="padding: 0 30px 30px 30px;">
                            <div style="background-color: #f9fafb; padding: 15px; border-radius: 5px;">
                                <p style="margin: 0 0 12px 0; font-size: 14px; font-weight: bold; color: #1f2937;">
                                    Informations de contact :
                                </p>
                                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                    @if ($boutique->adresse)
                                        <tr>
                                            <td
                                                style="padding: 5px 0; font-size: 14px; vertical-align: top; width: 30px; color: #4b5563;">
                                                📍
                                            </td>
                                            <td style="padding: 5px 0; font-size: 14px; color: #1f2937;">
                                                {{ $boutique->adresse }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($boutique->telephone)
                                        <tr>
                                            <td
                                                style="padding: 5px 0; font-size: 14px; vertical-align: top; color: #4b5563;">
                                                📞
                                            </td>
                                            <td style="padding: 5px 0; font-size: 14px; color: #1f2937;">
                                                {{ $boutique->telephone }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($boutique->email)
                                        <tr>
                                            <td
                                                style="padding: 5px 0; font-size: 14px; vertical-align: top; color: #4b5563;">
                                                ✉️
                                            </td>
                                            <td style="padding: 5px 0; font-size: 14px;">
                                                <a href="mailto:{{ $boutique->email }}"
                                                    style="color: #2563eb; text-decoration: none;">{{ $boutique->email }}</a>
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </div>
                        </td>
                    </tr>
                @endif

                <!-- Closing -->
                <tr>
                    <td style="padding: 0 30px 20px 30px;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
                            Merci de votre confiance !
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 0 30px 30px 30px;">
                        <p style="margin: 0; font-size: 14px; color: #4b5563; line-height: 1.6;">
                            Cordialement,<br>
                            <strong style="color: #1f2937;">L'équipe
                                {{ $boutique->nom ?? config('app.name') }}</strong>
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding: 20px 30px; border-top: 1px solid #e5e7eb; background-color: #f9fafb;">
                        <p style="margin: 0; font-size: 12px; color: #6b7280; line-height: 1.5; text-align: center;">
                            Ceci est un email automatique, merci de ne pas y répondre.<br>
                            Pour toute question, contactez-nous aux coordonnées ci-dessus.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
