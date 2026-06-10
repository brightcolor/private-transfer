<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'original_name',
        'storage_path',
        'mime_type',
        'size',
        'uploaded_size',
        'checksum',
        'upload_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'upload_completed_at' => 'datetime',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function isComplete(): bool
    {
        return $this->upload_completed_at !== null && $this->uploaded_size >= $this->size;
    }
}
