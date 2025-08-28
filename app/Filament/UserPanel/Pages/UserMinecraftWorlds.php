<?php

namespace App\Filament\UserPanel\Pages;

use App\Models\MinecraftWorld;
use App\Models\Server;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class UserMinecraftWorlds extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.user-panel.pages.user-minecraft-worlds';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'My Worlds';
    protected static ?string $title = 'My Worlds';
    protected static ?string $slug = 'my-worlds';


    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('New World')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->schema([
                    TextInput::make('name')
                        ->label('World Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('version')
                        ->label('Minecraft Version')
                        ->maxLength(50),
                    TextInput::make('seed')
                        ->label('World Seed')
                        ->maxLength(100)
                        ->nullable(),
                    Select::make('data_packs')
                        ->label('Data Packs')
                        ->multiple()
                        ->options([
                            'vanilla'       => 'Vanilla',
                            'custom_pack_1' => 'Custom Pack 1',
                            'custom_pack_2' => 'Custom Pack 2',
                        ])
                        ->nullable(),
                ])->action(function (array $data) {
                    MinecraftWorld::query()->create([
                        'name'       => $data['name'],
                        'version'    => $data['version'],
                        'seed'       => $data['seed'] ?? null,
                        'data_packs' => json_encode($data['data_packs']) ?? null,
                        'user_id'    => auth()->id(),
                    ]);
                })
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MinecraftWorld::query()
            )
            ->recordActions([
                Action::make('start')
                    ->label('Start')
                    ->color('success')
                    ->action(fn(Server $record) => $record->start()),

                Action::make('stop')
                    ->label('Stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn(Server $record) => $record->stop()),

                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('World Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('version')
                    ->label('Version')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ]);
    }
}
