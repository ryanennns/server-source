<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinecraftWorld extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CREATING = 'creating';
    public const STATUS_FINISHED = 'finished';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CREATING,
        self::STATUS_FINISHED,
    ];

    protected $guarded = [];

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
