<?php

namespace App\Jobs;

use App\Models\MinecraftWorld;
use App\Models\Server;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateMinecraftWorld implements ShouldQueue
{
    use Queueable;

    public function __construct(public MinecraftWorld $minecraftWorld)
    {
    }

    public function handle(): void
    {
        try {
            $server = Server::query()->createQuietly([
                'name'   => 'worldgen-' . $this->minecraftWorld->getKey(),
                'status' => Server::STATUS_PENDING,
            ]);

            $this->minecraftWorld->update(['server_id' => $server->getKey()]);

            CreateEc2::dispatchSync(
                $server,
                CreateEc2::INSTANCE_TYPE,
                Server::FABRIC_1211_CHUNK_GEN
            );

            $this->minecraftWorld->update(['status' => MinecraftWorld::HOST_PROVISIONED]);
        } catch (\Throwable $e) {
            $this->minecraftWorld->update(['status' => MinecraftWorld::STATUS_FAILED]);
            throw $e;
        }
    }
}
