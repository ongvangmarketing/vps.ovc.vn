<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';
    
    protected static ?string $navigationLabel = 'Servers';
    
    protected static ?string $modelLabel = 'Server';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ip')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('php_version')
                    ->disabled(),
                Forms\Components\TextInput::make('node_version')
                    ->disabled(),
                Forms\Components\TextInput::make('mysql_status')
                    ->disabled(),
                Forms\Components\TextInput::make('nginx_status')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'offline' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cpu_usage')
                    ->label('CPU'),
                Tables\Columns\TextColumn::make('ram_usage')
                    ->label('RAM'),
                Tables\Columns\TextColumn::make('last_checked_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('check_health')
                    ->label('Check Health')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Server $record) {
                        \Illuminate\Support\Facades\Artisan::call('vibe:health');
                        \Filament\Notifications\Notification::make()->title('Đang kiểm tra server...')->success()->send();
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }
}
