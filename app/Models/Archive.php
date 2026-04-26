<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Archive extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'disk_path',
        'size',
        'checksum',
        'status',
        'options',
        'user_id',
        'available_until',
    ];

    protected $casts = [
        'options' => 'array',
        'available_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (! $this->existsOnDisk()) {
            return null;
        }

        return Storage::disk($this->disk())->url($this->relativePath());
    }

    public function existsOnDisk(): bool
    {
        return Storage::disk($this->disk())->exists($this->relativePath());
    }

    public function disk(): string
    {
        return config('filesystems.archive_disk', config('filesystems.default'));
    }

    public function relativePath(): string
    {
        return $this->disk_path;
    }
}














