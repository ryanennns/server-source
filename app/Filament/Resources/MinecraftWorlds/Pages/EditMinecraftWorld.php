<?php

namespace App\Filament\Resources\MinecraftWorlds\Pages;

use App\Filament\Resources\MinecraftWorlds\MinecraftWorldResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMinecraftWorld extends EditRecord
{
    protected static string $resource = MinecraftWorldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
