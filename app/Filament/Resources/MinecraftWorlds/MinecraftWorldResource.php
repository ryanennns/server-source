<?php

namespace App\Filament\Resources\MinecraftWorlds;

use App\Filament\Resources\MinecraftWorlds\Pages\CreateMinecraftWorld;
use App\Filament\Resources\MinecraftWorlds\Pages\EditMinecraftWorld;
use App\Filament\Resources\MinecraftWorlds\Pages\ListMinecraftWorlds;
use App\Filament\Resources\MinecraftWorlds\Pages\ViewMinecraftWorld;
use App\Filament\Resources\MinecraftWorlds\Schemas\MinecraftWorldForm;
use App\Filament\Resources\MinecraftWorlds\Schemas\MinecraftWorldInfolist;
use App\Filament\Resources\MinecraftWorlds\Tables\MinecraftWorldsTable;
use App\Models\MinecraftWorld;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MinecraftWorldResource extends Resource
{
    protected static ?string $model = MinecraftWorld::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MinecraftWorldForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MinecraftWorldInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MinecraftWorldsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMinecraftWorlds::route('/'),
            'create' => CreateMinecraftWorld::route('/create'),
            'view' => ViewMinecraftWorld::route('/{record}'),
            'edit' => EditMinecraftWorld::route('/{record}/edit'),
        ];
    }
}
