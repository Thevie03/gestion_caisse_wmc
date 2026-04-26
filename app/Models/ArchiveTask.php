<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchiveTask extends Model
{
    use HasFactory;

    public const TYPE_EXPORT = 'export';
    public const TYPE_IMPORT = 'import';

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'type',
        'status',
        'progress',
        'message',
        'context',
        'user_id',
        'archive_id',
        'source_path',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'context' => 'array',
        'progress' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function archive(): BelongsTo
    {
        return $this->belongsTo(Archive::class);
    }

    public function markRunning(?string $message = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
            'progress' => 0,
            'message' => $this->normalizeEncoding($message),
        ])->save();
    }

    public function markProgress(int $progress, ?string $message = null, array $context = []): void
    {
        $this->forceFill([
            'progress' => max(0, min(100, $progress)),
            'message' => $this->normalizeEncoding($message ?? $this->message),
            'context' => $context ?: $this->context,
        ])->save();
    }

    public function markCompleted(?string $message = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'progress' => 100,
            'message' => $this->normalizeEncoding($message),
            'finished_at' => now(),
        ])->save();
    }

    public function markFailed(?string $message = null, array $context = []): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'message' => $this->normalizeEncoding($message),
            'context' => $context,
            'finished_at' => now(),
        ])->save();
    }

    protected function normalizeEncoding(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (mb_detect_encoding($value, 'UTF-8', true)) {
            return $value;
        }

        $encodings = ['UTF-8', 'ISO-8859-1', 'CP1252', 'CP850', 'ASCII'];

        return mb_convert_encoding($value, 'UTF-8', $encodings);
    }
}

