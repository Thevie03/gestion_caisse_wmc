<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchiveExportRequest;
use App\Http\Requests\ArchiveImportRequest;
use App\Models\Archive;
use App\Models\ArchiveTask;
use App\Services\ArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService
    ) {
        $this->middleware(['auth', 'verified', 'theme']);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Archive::class);

        $user = $request->user();
        $boutiqueActive = session('boutique_active');

        // Filtrer les archives selon le rôle et la boutique active
        $archivesQuery = Archive::with('user');

        if ($user->isSuperAdmin()) {
            // Super admin : filtrer par boutique active si une boutique est sélectionnée
            if ($boutiqueActive) {
                $archivesQuery->where(function($query) use ($boutiqueActive) {
                    $query->where('options->boutique_id', (int)$boutiqueActive)
                          ->orWhere('options->archive_system_data', true);
                });
            }
            // Sinon, voir toutes les archives (y compris toutes les boutiques et système)
        } elseif ($user->isOwner() && !$user->isSuperAdmin()) {
            // Propriétaires : voir uniquement les archives de leurs boutiques
            if ($boutiqueActive) {
                // Filtrer par la boutique active
                $archivesQuery->where('options->boutique_id', (int)$boutiqueActive)
                    ->where('user_id', $user->id);
            } else {
                // Si aucune boutique active, voir uniquement leurs propres archives
                $archivesQuery->where('user_id', $user->id);
            }
        } else {
            // Employés : voir uniquement les archives de leur boutique
            if ($user->boutique_id) {
                $archivesQuery->where('options->boutique_id', (int)$user->boutique_id)
                    ->where('user_id', $user->id);
            } else {
                // Si pas de boutique, voir uniquement leurs propres archives
                $archivesQuery->where('user_id', $user->id);
            }
        }

        $archives = $archivesQuery->latest()->paginate(10);

        // Filtrer les tâches également selon la boutique active
        $tasksQuery = ArchiveTask::with('user');
        if ($user->isSuperAdmin()) {
            // Super admin : filtrer par boutique active si une boutique est sélectionnée
            if ($boutiqueActive) {
                $tasksQuery->where(function($query) use ($boutiqueActive) {
                    $query->where('context->options->boutique_id', (int)$boutiqueActive)
                          ->orWhere('context->options->archive_system_data', true);
                });
            }
        } elseif ($user->isOwner() && !$user->isSuperAdmin()) {
            // Propriétaires : filtrer par boutique active
            if ($boutiqueActive) {
                $tasksQuery->where('context->options->boutique_id', (int)$boutiqueActive)
                    ->where('user_id', $user->id);
            } else {
                $tasksQuery->where('user_id', $user->id);
            }
        } else {
            // Employés : filtrer par leur boutique
            if ($user->boutique_id) {
                $tasksQuery->where('context->options->boutique_id', (int)$user->boutique_id)
                    ->where('user_id', $user->id);
            } else {
                $tasksQuery->where('user_id', $user->id);
            }
        }
        $tasks = $tasksQuery->latest()->limit(10)->get();

        // Récupérer la liste des boutiques pour l'interface selon le rôle
        $boutiques = null;
        if ($user->isSuperAdmin()) {
            // Super admin voit toutes les boutiques
            $boutiques = \Illuminate\Support\Facades\Cache::remember(
                'boutiques_actives_list',
                600, // 10 minutes
                function () {
                    return \App\Models\Boutique::select('id', 'nom')
                        ->where('actif', true)
                        ->orderBy('nom')
                        ->get();
                }
            );
        } elseif ($user->isOwner()) {
            // Propriétaires voient uniquement leurs boutiques assignées
            $boutiques = $user->ownedBoutiques()->where('actif', true)->orderBy('nom')->get();
            // Rétrocompatibilité : si aucune via many-to-many, utiliser boutique_id
            if ($boutiques->isEmpty() && $user->boutique_id) {
                $boutiques = \App\Models\Boutique::where('id', $user->boutique_id)
                    ->where('actif', true)
                    ->orderBy('nom')
                    ->get();
            }
        }

        return view('archives.index', compact('archives', 'tasks', 'boutiques'));
    }

    public function store(ArchiveExportRequest $request): RedirectResponse|JsonResponse|Response|BinaryFileResponse
    {
        $user = $request->user();

        // Déterminer le type d'archivage
        $boutiqueId = null;
        $archiveAllBoutiques = false;
        $archiveSystemData = false; // Archivage des données système uniquement

        if ($user->isOwner() && !$user->isSuperAdmin()) {
            // Propriétaire de boutique (même s'il est admin) : archiver uniquement ses boutiques assignées
            // Si plusieurs boutiques, utiliser la boutique active de la session
            // Sinon, utiliser la boutique_id par défaut
            $boutiqueId = session('boutique_active') ?? $user->boutique_id;

            // Vérifier que la boutique sélectionnée appartient bien au propriétaire
            if ($boutiqueId && !$user->ownsBoutique($boutiqueId)) {
                // Si la boutique en session n'appartient pas au propriétaire, utiliser la première boutique disponible
                $ownedBoutiques = $user->ownedBoutiques()->where('actif', true)->get();
                if ($ownedBoutiques->isEmpty() && $user->boutique_id) {
                    $boutiqueId = $user->boutique_id;
                } elseif ($ownedBoutiques->isNotEmpty()) {
                    $boutiqueId = $ownedBoutiques->first()->id;
                } else {
                    abort(403, 'Aucune boutique assignée.');
                }
            }

            $archiveSystemData = false;
            $archiveAllBoutiques = false;
        } elseif ($user->isSuperAdmin()) {
            // Super Admin : deux types d'archivage distincts
            $archiveType = $request->input('archive_type');

            if ($archiveType === 'system_data') {
                // Archivage des données système uniquement (contrats, paiements, abonnements, boutiques, etc.)
                $archiveSystemData = true;
                $boutiqueId = null;
                $archiveAllBoutiques = false;
            } elseif ($archiveType === 'all_boutiques') {
                // Archivage de toutes les boutiques
                $archiveSystemData = false;
                $boutiqueId = null;
                $archiveAllBoutiques = true;
            } elseif ($archiveType === 'boutique_specific') {
                // Archivage d'une boutique spécifique
                $archiveSystemData = false;
                $boutiqueId = $request->input('boutique_id');
                $archiveAllBoutiques = false;
            } else {
                // Par défaut, archiver toutes les boutiques si aucun type n'est spécifié
                $archiveSystemData = false;
                $boutiqueId = null;
                $archiveAllBoutiques = true;
            }
        }

        // Créer une tâche pour suivre la progression
        $task = ArchiveTask::create([
            'type' => ArchiveTask::TYPE_EXPORT,
            'status' => ArchiveTask::STATUS_PENDING,
            'user_id' => $user->id,
            'context' => [
                'options' => [
                    'encrypt' => $request->encrypt(),
                    'boutique_id' => $boutiqueId,
                    'archive_all_boutiques' => $archiveAllBoutiques,
                    'archive_system_data' => $archiveSystemData, // Nouveau : type d'archivage système
                ],
            ],
        ]);

        try {
            // Effectuer l'export de manière synchrone pour télécharger immédiatement
            $archive = $this->archiveService->performExport($task);

            // Récupérer le chemin absolu du fichier
            $disk = $this->archiveService->disk();

            // Obtenir le chemin absolu selon le type de disque
            // Le disque "archives" a root = storage_path('app/archives')
            // Le disk_path stocké est juste le nom du fichier
            if (method_exists($disk, 'path')) {
                $absolutePath = $disk->path($archive->disk_path);
            } else {
                // Pour le disque local "archives", utiliser storage_path
                $absolutePath = storage_path('app/archives/' . $archive->disk_path);
            }

            // Vérifier que le fichier existe et est dans le répertoire autorisé (sécurité)
            $realPath = realpath($absolutePath);
            $allowedPath = realpath(storage_path('app/archives'));

            if (!$realPath || !$allowedPath || !str_starts_with($realPath, $allowedPath) || !file_exists($realPath)) {
                // Essayer aussi avec le chemin complet (avec validation)
                $alternativePath = storage_path('app/archives/' . basename($archive->disk_path));
                $realAlternativePath = realpath($alternativePath);

                if ($realAlternativePath && $allowedPath && str_starts_with($realAlternativePath, $allowedPath) && file_exists($realAlternativePath)) {
                    $absolutePath = $realAlternativePath;
                } else {
                    \Illuminate\Support\Facades\Log::error('Tentative d\'accès à un fichier non autorisé', [
                        'user_id' => $user->id,
                        'archive_id' => $archive->id,
                        'requested_path' => $absolutePath
                    ]);
                    throw new \Exception('Le fichier d\'archive n\'a pas pu être trouvé.');
                }
            } else {
                $absolutePath = $realPath;
            }

            // Retourner le fichier en téléchargement direct
            return response()->download($absolutePath, $archive->filename, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(false); // Ne pas supprimer le fichier après envoi pour permettre de le télécharger à nouveau

        } catch (\Throwable $exception) {
            $task->markFailed($exception->getMessage());

            // Log de l'erreur pour la sécurité
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'archivage', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => config('app.debug')
                        ? 'Une erreur est survenue pendant l\'archivage: ' . $exception->getMessage()
                        : 'Une erreur est survenue pendant l\'archivage. Veuillez réessayer.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return Redirect::route('archives.index')
                ->with('error', config('app.debug')
                    ? 'Erreur lors de l\'archivage: ' . $exception->getMessage()
                    : 'Une erreur est survenue lors de l\'archivage. Veuillez réessayer.');
        }
    }

    public function download(Archive $archive)
    {
        $this->authorize('download', $archive);

        $disk = $this->archiveService->disk();

        if (! method_exists($disk, 'path') || ! $disk->exists($archive->disk_path)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $absolutePath = $disk->path($archive->disk_path);

        return response()->download($absolutePath, $archive->filename);
    }

    public function downloadLegacy(string $filename)
    {
        $sanitized = basename($filename);

        if ($sanitized !== $filename) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $archive = Archive::where('filename', $sanitized)->latest()->first();

        if (! $archive) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $this->download($archive);
    }

    public function destroy(Archive $archive): RedirectResponse
    {
        $this->authorize('delete', $archive);

        $disk = $this->archiveService->disk();
        if ($disk->exists($archive->disk_path)) {
            $disk->delete($archive->disk_path);
        }

        $archive->delete();

        return Redirect::route('archives.index')->with('success', 'Archive supprimée.');
    }

    public function createImport(): View
    {
        $this->authorize('import', Archive::class);

        $tasks = ArchiveTask::with('user')
            ->where('type', ArchiveTask::TYPE_IMPORT)
            ->latest()
            ->limit(5)
            ->get();

        return view('archives.import', compact('tasks'));
    }

    public function storeImport(ArchiveImportRequest $request): RedirectResponse
    {
        $this->authorize('import', Archive::class);

        $filename = sprintf(
            'import_%s_%s.zip',
            now()->format('YmdHis'),
            Str::random(8)
        );

        $relativePath = $request->file('archive')->storeAs(
            'imports',
            $filename,
            $this->archiveService->diskName()
        );

        $this->archiveService->dispatchImport(
            $request->user(),
            $relativePath,
            [
                'password' => $request->input('password'),
            ]
        );

        return Redirect::route('archives.index')
            ->with('success', 'Import lancé. Veuillez patienter pendant la restauration.');
    }
}
