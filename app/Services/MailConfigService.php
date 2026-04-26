<?php

namespace App\Services;

use App\Models\Boutique;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Service pour configurer dynamiquement les paramètres email
 * en fonction des paramètres de la boutique
 */
class MailConfigService
{
    /**
     * Configure le mailer Laravel avec les paramètres de la boutique
     *
     * @param Boutique $boutique La boutique dont on veut utiliser les paramètres email
     * @return void
     */
    public static function configureForBoutique(Boutique $boutique): void
    {
        // Si la boutique n'a pas de configuration email, utiliser les paramètres par défaut
        if (!$boutique->mail_mailer) {
            return;
        }

        // Configurer le mailer par défaut
        Config::set('mail.default', $boutique->mail_mailer);

        // Si c'est SMTP, configurer les paramètres SMTP
        if ($boutique->mail_mailer === 'smtp') {
            Config::set('mail.mailers.smtp.host', $boutique->mail_host ?? config('mail.mailers.smtp.host'));
            Config::set('mail.mailers.smtp.port', $boutique->mail_port ?? config('mail.mailers.smtp.port'));
            Config::set('mail.mailers.smtp.encryption', $boutique->mail_encryption ?? config('mail.mailers.smtp.encryption'));
            Config::set('mail.mailers.smtp.username', $boutique->mail_username ?? config('mail.mailers.smtp.username'));
            Config::set('mail.mailers.smtp.password', $boutique->mail_password ?? config('mail.mailers.smtp.password'));
        }

        // Configurer l'adresse et le nom de l'expéditeur
        if ($boutique->mail_from_address) {
            Config::set('mail.from.address', $boutique->mail_from_address);
        }
        if ($boutique->mail_from_name) {
            Config::set('mail.from.name', $boutique->mail_from_name);
        }
    }

    /**
     * Envoie un email en utilisant la configuration de la boutique
     *
     * @param Boutique $boutique La boutique dont on veut utiliser les paramètres email
     * @param mixed $mailable L'instance du Mailable à envoyer
     * @param string $to L'adresse email du destinataire
     * @return void
     */
    public static function sendForBoutique(Boutique $boutique, $mailable, string $to): void
    {
        // Configurer le mailer pour cette boutique
        self::configureForBoutique($boutique);

        // Envoyer l'email
        Mail::to($to)->send($mailable);
    }
}






