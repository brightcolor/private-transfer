<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_UPLOADING = 'uploading';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'public_token',
        'sender_email',
        'recipient_email',
        'message',
        'password_hash',
        'expires_at',
        'completed_at',
        'notification_sent_at',
        'max_downloads',
        'download_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'notification_sent_at' => 'datetime',
        ];
    }

    public function files(): HasMany
    {
        return $this->hasMany(TransferFile::class);
    }

    public function isAvailable(): bool
    {
        if ($this->status !== self::STATUS_COMPLETED || $this->expires_at->isPast()) {
            return false;
        }

        return $this->max_downloads === null || $this->download_count < $this->max_downloads;
    }

    public function isPasswordProtected(): bool
    {
        return filled($this->password_hash);
    }
}
