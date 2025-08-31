<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorldOptions extends Model
{
    use HasFactory;
    use HasUuids;

    public const V_1_21_8 = '1.21.8';

    public const VERSIONS = [
        self::V_1_21_8,
    ];

    public const WORLD_TYPE_VANILLA = 'vanilla';
    public const WORLD_TYPE_JJ = 'jjthunder';
    public const WORLD_TYPE_BIG_GLOBE = 'bigglobe';
    public const WORLD_TYPE_TERRATONIC = 'terratonic';

    public const WORLD_TYPES = [
        self::WORLD_TYPE_VANILLA,
        self::WORLD_TYPE_JJ,
        self::WORLD_TYPE_BIG_GLOBE,
        self::WORLD_TYPE_TERRATONIC,
    ];

    protected $guarded = [];

    public function minecraft_world(): BelongsTo
    {
        return $this->belongsTo(MinecraftWorld::class);
    }
}
