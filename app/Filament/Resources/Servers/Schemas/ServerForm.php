<?php

namespace App\Filament\Resources\Servers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('ip'),
                TextInput::make('port')
                    ->numeric(),
                TextInput::make('ec2_instance_id'),
                TextInput::make('region'),
                TextInput::make('instance_type'),
                TextInput::make('tags'),
            ]);
    }
}
