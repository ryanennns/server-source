<?php

namespace App\Http\Controllers;

use App\Models\MinecraftWorld;
use Illuminate\Http\Request;

class GetWorldGenSettingsController extends Controller
{
    public function __invoke(string $instanceId, Request $request)
    {
        $stuff = MinecraftWorld::query()
            ->whereHas('server', function ($query) use ($instanceId) {
                $query->where('ec2_instance_id', $instanceId);
            })->firstOrFail();

        return response()->json($stuff->options);
    }
}
