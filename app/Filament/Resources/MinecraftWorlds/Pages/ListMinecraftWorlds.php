<?php

namespace App\Filament\Resources\MinecraftWorlds\Pages;

use App\Filament\Resources\MinecraftWorlds\MinecraftWorldResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMinecraftWorlds extends ListRecords
{
    protected static string $resource = MinecraftWorldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
