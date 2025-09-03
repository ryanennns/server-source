<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_STARTING = 'starting';
    public const STATUS_STARTED = 'started';
    public const STATUS_STOPPING = 'stopping';
    public const STATUS_STOPPED = 'stopped';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_PENDING = 'pending';

    public const FABRIC_1211_CHUNK_GEN = 'ami-05617397aca0a3271';
    public const VANILLA_MINECRAFT_1218 = 'ami-055a5609252136ea9';
    public const MC_SERVER_SG = 'sg-0cc7a0084cc4aa94f';

    public const STATUSES = [
        self::STATUS_STARTING,
        self::STATUS_STARTED,
        self::STATUS_STOPPING,
        self::STATUS_STOPPED,
        self::STATUS_TERMINATED,
        self::STATUS_PENDING,
    ];

    protected $guarded = [];

    public function start(): bool
    {
        return $this->update(['status' => self::STATUS_STARTING]);
    }

    public function stop(): bool
    {
        return $this->update(['status' => self::STATUS_STOPPING]);
    }

    public function monthlyServerUsages(): HasMany
    {
        return $this->hasMany(MonthlyServerUsage::class);
    }
}
