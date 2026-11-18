<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EdukasiRequest extends Model
{
    protected $table = 'edukasi_requests';

    protected $fillable = [
        'user_id',
        'tema',
        'kategori',
        'preferred_type',
        'deskripsi',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
        'user_notified_read',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'user_notified_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
