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
    public const HOST_PROVISIONED = 'host_provisioned';
    public const SERVER_BOOTED = 'server_booted';
    public const CHUNKS_GENERATED = 'chunks_generated';
    public const DH_LODS_GENERATED = 'dh_lods_generated';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::SERVER_BOOTED,
        self::CHUNKS_GENERATED,
        self::DH_LODS_GENERATED,
        self::STATUS_FINISHED,
    ];

    protected $guarded = [];

    private const SERVER_BOOT_REGEX = '[Server thread/INFO]: Done';

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function hasServerBooted(): bool
    {
        Log::info("Checking if world server has booted", [
            'log_contents' => Storage::disk('s3')->get(
                "{$this->server->ec2_instance_id}-latest.log"
            ),
        ]);

        return Str::contains(
            Storage::disk('s3')->get("{$this->server->ec2_instance_id}-latest.log"),
            self::SERVER_BOOT_REGEX
        );
    }

    public function hasGeneratedChunks(): bool
    {
        return false;
    }

    public function hasGeneratedDH(): bool
    {
        return false;
    }

    public function updateStatus(): void
    {
        $serverHasBooted = $this->hasServerBooted();
        $serverHasGeneratedChunks = $this->server->hasGeneratedChunks();
        $serverHasGeneratedDH = $this->server->hasGeneratedDH();
        Log::info("Updating world status", [
            'server_has_booted'           => $serverHasBooted,
            'server_has_generated_chunks' => $serverHasGeneratedChunks,
            'server_has_generated_dh'     => $serverHasGeneratedDH,
        ]);

        if ($this->status === self::STATUS_PENDING && $serverHasBooted) {
            $this->update(['status' => self::SERVER_BOOTED]);
        }

        if ($this->status === self::SERVER_BOOTED && $serverHasGeneratedChunks) {
            $this->update(['status' => self::CHUNKS_GENERATED]);
        }

        if ($this->status === self::CHUNKS_GENERATED && $serverHasGeneratedDH) {
            $this->update(['status' => self::DH_LODS_GENERATED]);
        }

        if ($this->status === self::DH_LODS_GENERATED && $this->status !== self::STATUS_FINISHED) {
            $this->update(['status' => self::STATUS_FINISHED]);
            $this->server->terminateInstance();
        }

        Log::info("World status updated", ['new_status' => $this->status]);
    }
}
