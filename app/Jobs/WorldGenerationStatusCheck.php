<?php

namespace App\Jobs;

use App\Models\MinecraftWorld;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class WorldGenerationStatusCheck implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        MinecraftWorld::query()
            ->whereNotIn(
                'status',
                [MinecraftWorld::STATUS_PENDING, MinecraftWorld::STATUS_FINISHED, MinecraftWorld::STATUS_FAILED]
            )
            ->chunk(
                10,
                fn(Collection $worlds) => $worlds->each(
                    fn(MinecraftWorld $world) => $world->updateStatus()
                )
            );
    }
}
