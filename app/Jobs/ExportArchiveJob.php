<?php

namespace App\Jobs;

use App\Models\ArchiveTask;
use App\Services\ArchiveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ExportArchiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ArchiveTask $task)
    {
        $this->onQueue(config('queue.archive_queue', 'default'));
    }

    public function handle(ArchiveService $archiveService): void
    {
        $task = $this->task->fresh() ?? $this->task;

        try {
            $archiveService->performExport($task);
        } catch (Throwable $exception) {
            $task->markFailed($exception->getMessage());
            report($exception);

            throw $exception;
        }
    }
}














