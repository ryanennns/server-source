<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    private const PHASE_ONE_REGEX = '[Server thread/INFO]: Done';

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function isFinishedGenerating(): bool
    {
        Log::info("Checking if world $this->id is finished generating.", [
            'log_contents' => Storage::disk('s3')->get(
                "{$this->server->ec2_instance_id}-latest.log"
            ),
        ]);

        return Str::contains(
            Storage::disk('s3')->get("{$this->server->ec2_instance_id}-latest.log"),
            self::PHASE_ONE_REGEX
        );
    }
}
