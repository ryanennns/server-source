<?php

namespace App\Filament\Resources\MinecraftWorlds\Pages;

use App\Filament\Resources\MinecraftWorlds\MinecraftWorldResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMinecraftWorld extends ViewRecord
{
    protected static string $resource = MinecraftWorldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
