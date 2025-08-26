<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;
    use HasUuids;

    public const STATUS_STARTING = 'starting';
    public const STATUS_STARTED = 'started';
    public const STATUS_STOPPING = 'stopping';
    public const STATUS_STOPPED = 'stopped';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_PENDING = 'pending';

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
}
