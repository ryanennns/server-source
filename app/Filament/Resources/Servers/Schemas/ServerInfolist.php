<?php

namespace App\Filament\Resources\Servers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ServerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('name'),
                TextEntry::make('ip'),
                TextEntry::make('port')
                    ->numeric(),
                TextEntry::make('ec2_instance_id'),
                TextEntry::make('region'),
                TextEntry::make('instance_type'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
