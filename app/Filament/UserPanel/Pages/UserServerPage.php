<?php

namespace App\Filament\UserPanel\Pages;

use App\Models\Server;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class UserServerPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.user-panel.pages.user-server-page';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'My Servers';
    protected static ?string $slug = 'my-servers';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('New Server')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->schema([
                    TextInput::make('name')
                        ->label('Server Name')
                        ->required()
                        ->maxLength(255),

                    Select::make('region')
                        ->label('Region')
                        ->options([
                            'us-east-1'    => 'US East (N. Virginia)',
                            'us-west-2'    => 'US West (Oregon)',
                            'eu-central-1' => 'EU (Frankfurt)',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    Server::create([
                        'name'    => $data['name'],
                        'region'  => $data['region'],
                        'status'  => Server::STATUS_PENDING,
                        'user_id' => auth()->id(),
                    ]);

                    $this->notify('success', 'Server creation started!');
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Server::query()
            )
            ->recordActions(
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
