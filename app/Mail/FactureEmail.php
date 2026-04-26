<?php

namespace App\Mail;

use App\Models\Facture;
use App\Models\Boutique;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Mailable pour l'envoi de factures par email
 *
 * Cette classe gère l'envoi de factures aux clients par email
 * avec la facture en pièce jointe au format PDF
 */
class FactureEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * La facture à envoyer
     */
    public $facture;

    /**
     * La boutique
     */
    public $boutique;

    /**
     * Le client
     */
    public $client;

    /**
     * Create a new message instance.
     *
     * @param Facture $facture La facture à envoyer
     * @return void
     */
    public function __construct(Facture $facture)
    {
        // Toujours s'assurer que toutes les relations nécessaires sont chargées
        // avec toutes les colonnes requises pour l'email
        $facture->load([
            'vente.user:id,name',
            'vente.boutique:id,nom,theme_color,logo,adresse,telephone,email,mail_mailer,mail_host,mail_port,mail_username,mail_password,mail_encryption,mail_from_address,mail_from_name',
            'vente.details.produit:id,nom',
            'vente.client:id,nom,prenom,email,telephone'
        ]);

        $this->facture = $facture;
        $this->boutique = $facture->vente->boutique;
        $this->client = $facture->vente->client;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        $boutiqueNom = $this->boutique->nom ?? 'Notre Boutique';
        $numeroFacture = $this->facture->numero_facture;

        $fromAddress = $this->boutique->mail_from_address ?? $this->boutique->email ?? config('mail.from.address');
        $fromName = $this->boutique->mail_from_name ?? $this->boutique->nom ?? config('mail.from.name');

        return new Envelope(
            subject: "Facture {$numeroFacture} - {$boutiqueNom}",
            from: new Address($fromAddress, $fromName),
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        // Rendre la vue en HTML pour forcer l'envoi en format HTML
        $htmlContent = view('emails.facture', [
            'facture' => $this->facture,
            'boutique' => $this->boutique,
            'client' => $this->client,
        ])->render();

        return new Content(
            htmlString: $htmlContent,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        // Générer le PDF de la facture de manière optimisée
        $themeColor = $this->boutique->theme_color ?? '#475569';

        // S'assurer que toutes les relations nécessaires sont chargées
        if (!$this->facture->relationLoaded('vente.details.produit')) {
            $this->facture->load('vente.details.produit:id,nom');
        }
        if (!$this->facture->relationLoaded('vente.user')) {
            $this->facture->load('vente.user:id,name');
        }

        $pdf = Pdf::loadView('factures.pdf', [
            'facture' => $this->facture,
            'boutique' => $this->boutique,
            'themeColor' => $themeColor
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'Arial'
        ]);

        $filename = 'Facture_' . $this->facture->numero_facture . '_' . date('Y-m-d') . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
