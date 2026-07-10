<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OfflineSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API de synchronisation des opérations créées hors connexion.
 */
class OfflineSyncController extends Controller
{
    public function __construct(private OfflineSyncService $syncService)
    {
    }

    /**
     * Synchroniser un lot d'opérations (clients, ventes, stock).
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'operations' => 'required|array|min:1|max:50',
            'operations.*.uuid' => 'required|uuid',
            'operations.*.type' => 'required|in:client,vente,stock',
            'operations.*.payload' => 'required|array',
        ]);

        $result = $this->syncService->processBatch(
            $request->user(),
            $validated['operations']
        );

        return response()->json([
            'success' => empty($result['errors']),
            'results' => $result['results'],
            'errors' => $result['errors'],
            'synced_at' => now()->toIso8601String(),
        ], empty($result['errors']) ? 200 : 207);
    }

    /**
     * Synchroniser une vente unique (idempotente via offline_uuid).
     */
    public function syncVente(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
            'payload' => 'required|array',
            'payload.produits' => 'required|array|min:1',
            'payload.produits.*.id' => 'required|integer',
            'payload.produits.*.quantite' => 'required|integer|min:1',
        ]);

        try {
            $result = $this->syncService->syncVente(
                $request->user(),
                $validated['uuid'],
                $validated['payload']
            );

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Synchroniser un client unique (idempotent via offline_uuid).
     */
    public function syncClient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
            'payload' => 'required|array',
            'payload.prenom' => 'required|string|max:255',
        ]);

        try {
            $result = $this->syncService->syncClient(
                $request->user(),
                $validated['uuid'],
                $validated['payload']
            );

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
