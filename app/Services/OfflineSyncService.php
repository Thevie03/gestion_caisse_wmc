<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Facture;
use App\Models\PaiementVente;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use App\Models\VenteDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Service de synchronisation des opérations créées hors connexion.
 */
class OfflineSyncService
{
    /**
     * Traiter un lot d'opérations offline (clients puis ventes).
     *
     * @param  array<int, array{uuid: string, type: string, payload: array}>  $operations
     * @return array{results: array<int, array>, errors: array<int, array>}
     */
    public function processBatch(User $user, array $operations): array
    {
        $results = [];
        $errors = [];
        $clientUuidMap = [];

        $sorted = collect($operations)->sortBy(function ($op) {
            $priority = match ($op['type'] ?? '') {
                'client' => 1,
                'vente' => 2,
                'stock' => 3,
                default => 9,
            };

            return [$priority, $op['payload']['created_at'] ?? $op['uuid'] ?? ''];
        });

        foreach ($sorted as $operation) {
            $uuid = $operation['uuid'] ?? null;
            $type = $operation['type'] ?? null;
            $payload = $operation['payload'] ?? [];

            if (!$uuid || !$type) {
                $errors[] = [
                    'uuid' => $uuid,
                    'message' => 'Opération invalide (uuid ou type manquant).',
                ];
                continue;
            }

            try {
                $result = match ($type) {
                    'client' => $this->syncClient($user, $uuid, $payload),
                    'vente' => $this->syncVente($user, $uuid, $payload, $clientUuidMap),
                    'stock' => $this->syncStock($user, $uuid, $payload),
                    default => throw ValidationException::withMessages([
                        'type' => "Type d'opération non supporté : {$type}",
                    ]),
                };

                if ($type === 'client') {
                    $clientUuidMap[$uuid] = $result['id'];
                }

                $results[] = array_merge(['uuid' => $uuid, 'type' => $type, 'success' => true], $result);
            } catch (\Throwable $e) {
                $errors[] = [
                    'uuid' => $uuid,
                    'type' => $type,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return compact('results', 'errors');
    }

    /**
     * Créer ou retrouver un client synchronisé (idempotent via offline_uuid).
     */
    public function syncClient(User $user, string $offlineUuid, array $payload): array
    {
        $existing = Client::withoutGlobalScopes()
            ->where('offline_uuid', $offlineUuid)
            ->first();

        if ($existing) {
            return [
                'id' => $existing->id,
                'duplicate' => true,
                'nom_complet' => $existing->nom_complet,
            ];
        }

        $boutiqueId = $this->resolveBoutiqueId($user, $payload['boutique_id'] ?? null);

        $client = Client::create([
            'offline_uuid' => $offlineUuid,
            'nom' => $payload['nom'] ?? null,
            'prenom' => $payload['prenom'] ?? 'Client',
            'email' => $payload['email'] ?? null,
            'telephone' => $payload['telephone'] ?? null,
            'adresse' => $payload['adresse'] ?? null,
            'ville' => $payload['ville'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'actif' => true,
            'user_id' => $user->id,
            'boutique_id' => $boutiqueId,
        ]);

        return [
            'id' => $client->id,
            'duplicate' => false,
            'nom_complet' => $client->nom_complet,
        ];
    }

    /**
     * Créer une vente synchronisée (idempotent via offline_uuid).
     *
     * @param  array<string, int>  $clientUuidMap
     */
    public function syncVente(User $user, string $offlineUuid, array $payload, array $clientUuidMap = []): array
    {
        $existing = Vente::withoutGlobalScopes()
            ->where('offline_uuid', $offlineUuid)
            ->first();

        if ($existing) {
            return [
                'id' => $existing->id,
                'numero_vente' => $existing->numero_vente,
                'duplicate' => true,
            ];
        }

        $boutiqueId = $this->resolveBoutiqueId($user, $payload['boutique_id'] ?? null);
        $produits = $payload['produits'] ?? [];

        if (empty($produits)) {
            throw ValidationException::withMessages(['produits' => 'La vente doit contenir au moins un produit.']);
        }

        return DB::transaction(function () use ($user, $offlineUuid, $payload, $boutiqueId, $produits, $clientUuidMap) {
            $total = 0;
            $produitsVendus = [];

            foreach ($produits as $produitData) {
                $produit = Produit::withoutGlobalScopes()
                    ->where('id', $produitData['id'])
                    ->where('boutique_id', $boutiqueId)
                    ->firstOrFail();

                $quantite = (int) ($produitData['quantite'] ?? 1);

                if ($produit->quantite_stock > 0 && $produit->quantite_stock < $quantite) {
                    throw new \RuntimeException(
                        "Stock insuffisant pour {$produit->nom}. Disponible : {$produit->quantite_stock}"
                    );
                }

                $prixUnitaire = isset($produitData['prix_unitaire'])
                    ? round((float) $produitData['prix_unitaire'])
                    : round((float) $produit->prix_vente);

                $sousTotal = round($prixUnitaire * $quantite);
                $total = round($total + $sousTotal);

                $produitsVendus[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total' => $sousTotal,
                ];
            }

            $remise = round((float) ($payload['remise'] ?? 0));
            $totalFinal = round($total - $remise);

            $paiementsCollecte = collect($payload['paiements'] ?? [])
                ->map(fn ($p) => [
                    'mode' => $p['mode'] ?? null,
                    'montant' => isset($p['montant']) ? round((float) $p['montant']) : 0,
                    'notes' => $p['notes'] ?? null,
                ])
                ->filter(fn ($p) => $p['mode'] && $p['montant'] > 0);

            if ($paiementsCollecte->isEmpty()) {
                $paiementsCollecte = collect([[
                    'mode' => 'especes',
                    'montant' => $totalFinal,
                    'notes' => 'Paiement offline synchronisé',
                ]]);
            }

            $montantPaye = $paiementsCollecte->sum('montant');
            $soldeRestant = max($totalFinal - $montantPaye, 0);
            $statutPaiement = $soldeRestant > 0 ? 'partiel' : 'complet';
            $modePrincipal = $paiementsCollecte->first()['mode'] ?? 'especes';

            $clientId = $this->resolveClientId($payload, $clientUuidMap);

            $vente = Vente::create([
                'offline_uuid' => $offlineUuid,
                'user_id' => $user->id,
                'boutique_id' => $boutiqueId,
                'client_id' => $clientId,
                'total' => $total,
                'remise' => $remise,
                'total_final' => $totalFinal,
                'montant_paye' => $montantPaye,
                'solde_restant' => $soldeRestant,
                'statut_paiement' => $statutPaiement,
                'mode_paiement' => $modePrincipal,
                'numero_vente' => '',
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($paiementsCollecte as $index => $paiement) {
                PaiementVente::create([
                    'vente_id' => $vente->id,
                    'montant' => $paiement['montant'],
                    'mode_paiement' => $paiement['mode'],
                    'notes' => $paiement['notes'] ?? 'Paiement sync #' . ($index + 1),
                    'user_id' => $user->id,
                ]);
            }

            foreach ($produitsVendus as $produitVendu) {
                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $produitVendu['produit']->id,
                    'quantite' => $produitVendu['quantite'],
                    'prix_unitaire' => $produitVendu['prix_unitaire'],
                    'sous_total' => $produitVendu['sous_total'],
                ]);

                if ($produitVendu['produit']->quantite_stock > 0) {
                    $produitVendu['produit']->decrement('quantite_stock', $produitVendu['quantite']);
                }

                $mouvement = $produitVendu['produit']->mouvementsStock()->create([
                    'type' => 'sortie',
                    'quantite' => $produitVendu['quantite'],
                    'motif' => 'Vente offline #' . $vente->numero_vente,
                    'user_id' => $user->id,
                    'boutique_id' => $boutiqueId,
                ]);

                NotificationService::notifierMouvementStock($mouvement, $produitVendu['produit'], $user);
            }

            if (!$vente->facture) {
                Facture::create([
                    'vente_id' => $vente->id,
                    'numero_facture' => $this->genererNumeroFacture($boutiqueId),
                    'lien_pdf' => null,
                ]);
            }

            $this->invaliderCacheDashboard($boutiqueId, $user->id);

            return [
                'id' => $vente->id,
                'numero_vente' => $vente->numero_vente,
                'duplicate' => false,
            ];
        });
    }

    /**
     * Ajuster le stock depuis une opération offline.
     */
    public function syncStock(User $user, string $offlineUuid, array $payload): array
    {
        $produitId = $payload['produit_id'] ?? null;
        $quantite = (int) ($payload['quantite'] ?? 0);
        $type = $payload['type'] ?? 'sortie';

        if (!$produitId || $quantite <= 0) {
            throw ValidationException::withMessages(['stock' => 'Données stock invalides.']);
        }

        $boutiqueId = $this->resolveBoutiqueId($user, $payload['boutique_id'] ?? null);

        $produit = Produit::withoutGlobalScopes()
            ->where('id', $produitId)
            ->where('boutique_id', $boutiqueId)
            ->firstOrFail();

        if ($type === 'sortie') {
            $produit->decrement('quantite_stock', min($quantite, $produit->quantite_stock));
        } else {
            $produit->increment('quantite_stock', $quantite);
        }

        $mouvement = $produit->mouvementsStock()->create([
            'type' => $type,
            'quantite' => $quantite,
            'motif' => $payload['motif'] ?? 'Ajustement offline synchronisé',
            'user_id' => $user->id,
            'boutique_id' => $boutiqueId,
        ]);

        NotificationService::notifierMouvementStock($mouvement, $produit, $user);

        return [
            'produit_id' => $produit->id,
            'quantite_stock' => $produit->fresh()->quantite_stock,
        ];
    }

    /**
     * @param  array<string, int>  $clientUuidMap
     */
    private function resolveClientId(array $payload, array $clientUuidMap): ?int
    {
        if (!empty($payload['client_offline_uuid'])) {
            $offlineUuid = $payload['client_offline_uuid'];

            if (isset($clientUuidMap[$offlineUuid])) {
                return $clientUuidMap[$offlineUuid];
            }

            $client = Client::withoutGlobalScopes()
                ->where('offline_uuid', $offlineUuid)
                ->first();

            return $client?->id;
        }

        return !empty($payload['client_id']) ? (int) $payload['client_id'] : null;
    }

    private function resolveBoutiqueId(User $user, ?int $payloadBoutiqueId): int
    {
        if ($user->isEmploye()) {
            return (int) $user->boutique_id;
        }

        $sessionId = session('boutique_active');

        if ($sessionId) {
            return (int) $sessionId;
        }

        if ($payloadBoutiqueId) {
            return (int) $payloadBoutiqueId;
        }

        if ($user->boutique_id) {
            return (int) $user->boutique_id;
        }

        throw ValidationException::withMessages([
            'boutique_id' => 'Aucune boutique active pour la synchronisation.',
        ]);
    }

    private function genererNumeroFacture(?int $boutiqueId): string
    {
        $prefixe = 'FAC';
        $annee = date('Y');
        $mois = date('m');

        $query = Facture::whereYear('created_at', $annee)->whereMonth('created_at', $mois);

        if ($boutiqueId) {
            $query->whereHas('vente', fn ($q) => $q->where('boutique_id', $boutiqueId));
        }

        $dernierNumero = $query->orderBy('numero_facture', 'desc')->value('numero_facture');
        $nombre = $dernierNumero ? ((int) substr($dernierNumero, -4)) + 1 : 1;

        $numeroFacture = $prefixe . $annee . $mois . str_pad($nombre, 4, '0', STR_PAD_LEFT);
        $tentatives = 0;

        while (Facture::where('numero_facture', $numeroFacture)->exists() && $tentatives < 100) {
            $nombre++;
            $numeroFacture = $prefixe . $annee . $mois . str_pad($nombre, 4, '0', STR_PAD_LEFT);
            $tentatives++;
        }

        return $numeroFacture;
    }

    private function invaliderCacheDashboard(int $boutiqueId, int $userId): void
    {
        $keys = [
            'dashboard_stats_',
            'dashboard_totals_',
            'dashboard_ventes_chart_',
            'dashboard_produits_chart_',
            'dashboard_dernieres_ventes_',
            'dashboard_produits_rupture_',
            'dashboard_top_produits_',
        ];

        foreach ($keys as $prefix) {
            \Illuminate\Support\Facades\Cache::forget($prefix . $userId . '_' . $boutiqueId);
            \Illuminate\Support\Facades\Cache::forget($prefix . $userId . '_all');
        }
    }
}
