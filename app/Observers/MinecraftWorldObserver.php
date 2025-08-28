<?php

namespace App\Observers;

use App\Jobs\GenerateMinecraftWorld;
use App\Models\MinecraftWorld;

class MinecraftWorldObserver
{
    public function created(MinecraftWorld $minecraftWorld): void
    {
        GenerateMinecraftWorld::dispatch($minecraftWorld);
    }
}
