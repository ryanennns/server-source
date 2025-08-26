<?php

namespace App\Filament\Resources\Servers;

use App\Filament\Resources\Servers\Pages\CreateServer;
use App\Filament\Resources\Servers\Pages\EditServer;
use App\Filament\Resources\Servers\Pages\ListServers;
use App\Filament\Resources\Servers\Pages\ViewServer;
use App\Filament\Resources\Servers\Schemas\ServerForm;
use App\Filament\Resources\Servers\Schemas\ServerInfolist;
use App\Filament\Resources\Servers\Tables\ServersTable;
use App\Models\Server;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ServerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServersTable::configure($table);
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
            'index' => ListServers::route('/'),
            'create' => CreateServer::route('/create'),
            'view' => ViewServer::route('/{record}'),
            'edit' => EditServer::route('/{record}/edit'),
        ];
    }
}
