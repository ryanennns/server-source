<?php

namespace App\Filament\UserPanel\Pages;

use App\Models\Server;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class UserServerPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.user-panel.pages.user-server-page';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'My Servers';
    protected static ?string $slug = 'my-servers';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Server::query()
            )
            ->actions(
                [
                    Action::make('start')
                        ->label('Start')
                        ->visible(fn(Server $record) => $record->status === Server::STATUS_STOPPED)
                        ->color('success')
                        ->action(fn(Server $record) => $record->start()),

                    Action::make('stop')
                        ->label('Stop')
                        ->visible(fn(Server $record) => $record->status === Server::STATUS_STARTED)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Server $record) => $record->stop()),

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn(Server $record) => $record->delete()),
                ]
            )->columns([
                TextColumn::make('name')
                    ->label('Server Name')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'running',
                        'danger'  => 'stopped',
                        'warning' => 'pending',
                    ]),

                TextColumn::make('ip')
                    ->label('IP Address')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])->paginated([10, 25]);
    }
}
