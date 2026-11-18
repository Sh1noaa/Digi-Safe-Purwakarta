<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function addressee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }

    public static function between(int $userA, int $userB): ?self
    {
        return static::where(function ($q) use ($userA, $userB) {
            $q->where('requester_id', $userA)->where('addressee_id', $userB);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('requester_id', $userB)->where('addressee_id', $userA);
        })->first();
    }

    /**
     * Ringkas status hubungan dari sudut pandang $viewerId terhadap $targetId.
     */
    public static function relationFor(int $viewerId, int $targetId): array
    {
        if ($viewerId === $targetId) {
            return ['status' => 'self', 'friendship' => null];
        }

        $f = static::between($viewerId, $targetId);
        if (! $f) {
            return ['status' => 'none', 'friendship' => null];
        }

        if ($f->status === 'accepted') {
            return ['status' => 'accepted', 'friendship' => $f];
        }

        if ($f->status === 'declined') {
            return ['status' => 'declined', 'friendship' => $f];
        }

        return [
            'status' => (int) $f->requester_id === $viewerId ? 'pending_outgoing' : 'pending_incoming',
            'friendship' => $f,
        ];
    }
}

