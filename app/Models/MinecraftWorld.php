<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MinecraftWorld extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const HOST_PROVISIONED = 'host_provisioned';
    public const SERVER_BOOTED = 'server_booted';
    public const CHUNKS_GENERATED = 'chunks_generated';
    public const DH_LODS_GENERATED = 'dh_lods_generated';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::HOST_PROVISIONED,
        self::SERVER_BOOTED,
        self::CHUNKS_GENERATED,
        self::DH_LODS_GENERATED,
        self::STATUS_FINISHED,
        self::STATUS_FAILED,
    ];

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
    ];

    public const SERVER_BOOT_REGEX = '/\[Server thread\/INFO\]: Done (.*)! For help, type \"help\"/';
    public const CHUNKS_GENERATED_REGEX = "/\[Server thread\/INFO\]: \[Chunky\] Task finished for minecraft:overworld. Processed:/";

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany('world_options');
    }

    public function hasServerBooted(): bool
    {
        Log::info("Checking if world server has booted", [
            'log_contents' => Storage::disk('s3')->get(
                "{$this->server->ec2_instance_id}-latest.log"
            ),
        ]);

        return !!preg_match(
            self::SERVER_BOOT_REGEX,
            Storage::disk('s3')->get("{$this->server->ec2_instance_id}-latest.log")
        );
    }

    public function hasGeneratedChunks(): bool
    {
        Log::info("Checking if world has generated chunks", [
            'log_contents' => Storage::disk('s3')->get(
                "{$this->server->ec2_instance_id}-latest.log"
            ),
        ]);

        return !!preg_match(
            self::CHUNKS_GENERATED_REGEX,
            Storage::disk('s3')->get("{$this->server->ec2_instance_id}-latest.log")
        );
    }

    public function hasGeneratedDH(): bool
    {
        return false;
    }

    public function updateStatus(): void
    {
        $serverHasBooted = $this->hasServerBooted();
        $serverHasGeneratedChunks = $this->hasGeneratedChunks();
        $serverHasGeneratedDH = $this->hasGeneratedDH();
        Log::info("Updating world status", [
            'server_has_booted'           => $serverHasBooted,
            'server_has_generated_chunks' => $serverHasGeneratedChunks,
            'server_has_generated_dh'     => $serverHasGeneratedDH,
        ]);

        if ($serverHasGeneratedDH) {
            $this->update(['status' => self::DH_LODS_GENERATED]);

            return;
        }

        if ($serverHasGeneratedChunks) {
            $this->update(['status' => self::CHUNKS_GENERATED]);

            return;
        }

        if ($serverHasBooted) {
            $this->update(['status' => self::SERVER_BOOTED]);

            return;
        }
    }
}
