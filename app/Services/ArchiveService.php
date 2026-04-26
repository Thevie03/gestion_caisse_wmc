<?php

namespace App\Services;

use App\Jobs\ExportArchiveJob;
use App\Jobs\ImportArchiveJob;
use App\Models\Archive;
use App\Models\ArchiveTask;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ArchiveService
{
    public function __construct(
        protected LocalArchiveService $localArchiveService
    ) {
    }

    public function dispatchExport(User $user, array $options = []): ArchiveTask
    {
        $task = ArchiveTask::create([
            'type' => ArchiveTask::TYPE_EXPORT,
            'status' => ArchiveTask::STATUS_PENDING,
            'user_id' => $user->id,
            'context' => ['options' => $options],
        ]);

        dispatch(new ExportArchiveJob($task));

        return $task;
    }

    public function dispatchImport(User $user, string $relativePath, array $options = []): ArchiveTask
    {
        $task = ArchiveTask::create([
            'type' => ArchiveTask::TYPE_IMPORT,
            'status' => ArchiveTask::STATUS_PENDING,
            'user_id' => $user->id,
            'source_path' => $relativePath,
            'context' => ['options' => $options],
        ]);

        dispatch(new ImportArchiveJob($task));

        return $task;
    }

    public function performExport(ArchiveTask $task): Archive
    {
        $options = Arr::get($task->context, 'options', []);

        // Transmettre toutes les options d'archivage
        $options['boutique_id'] = Arr::get($task->context, 'options.boutique_id');
        $options['archive_all_boutiques'] = Arr::get($task->context, 'options.archive_all_boutiques', false);
        $options['archive_system_data'] = Arr::get($task->context, 'options.archive_system_data', false);

        $task->markRunning('Initialisation de l\'export...');

        $result = $this->localArchiveService->archiveData(
            $options,
            function (int $percent, string $message) use ($task) {
                $task->markProgress($percent, $message);
            }
        );

        // Vérifier si une archive avec le même checksum existe déjà (éviter les doublons)
        $existingArchive = Archive::where('checksum', $result['checksum'])
            ->where('user_id', $task->user_id)
            ->where('filename', $result['filename'])
            ->first();

        if ($existingArchive) {
            // Si une archive identique existe déjà, réutiliser celle-ci au lieu d'en créer une nouvelle
            $archive = $existingArchive;
            // Mettre à jour la tâche pour qu'elle pointe vers l'archive existante
            $task->archive()->associate($archive);
            $task->markCompleted('Export terminé (archive existante réutilisée).');
        } else {
            // Créer une nouvelle archive uniquement si elle n'existe pas déjà
            $archive = Archive::create([
                'filename' => $result['filename'],
                'disk_path' => $result['relative_path'],
                'size' => $result['size'],
                'checksum' => $result['checksum'],
                'user_id' => $task->user_id,
                'status' => 'completed',
                'options' => [
                    'boutique_id' => Arr::get($options, 'boutique_id'),
                    'archive_all_boutiques' => Arr::get($options, 'archive_all_boutiques', false),
                    'archive_system_data' => Arr::get($options, 'archive_system_data', false),
                    'encrypt' => Arr::get($options, 'encrypt', false),
                ],
            ]);

            $task->archive()->associate($archive);
            $task->markCompleted('Export terminé.');
        }

        $task->archive()->associate($archive);
        $task->markCompleted('Export terminé.');

        return $archive;
    }

    public function performImport(ArchiveTask $task): array
    {
        $options = Arr::get($task->context, 'options', []);
        $task->markRunning('Initialisation de l’import…');

        $relativePath = $task->source_path;

        if (! $relativePath) {
            throw new \RuntimeException('Aucun fichier d’archive n’est associé à cette tâche.');
        }

        $absolute = $this->disk()->path($relativePath);

        $result = $this->localArchiveService->restoreFromArchive(
            $absolute,
            $options,
            function (int $percent, string $message) use ($task) {
                $task->markProgress($percent, $message);
            }
        );

        if (! Arr::get($options, 'keep_source', false)) {
            $this->disk()->delete($relativePath);
        }

        $task->markCompleted('Import terminé.');

        return $result;
    }

    public function diskName(): string
    {
        return config('filesystems.archive_disk', config('filesystems.default'));
    }

    public function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }
}

