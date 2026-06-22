<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeploymentLogResource\Pages;
use App\Models\DeploymentLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeploymentLogResource extends Resource
{
    protected static ?string $model = DeploymentLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Logs';
    
    protected static ?string $modelLabel = 'Deployment Log';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('trigger_type')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\DateTimePicker::make('started_at')->disabled(),
                Forms\Components\DateTimePicker::make('finished_at')->disabled(),
                Forms\Components\Textarea::make('output')->disabled()->columnSpanFull()->rows(15),
                Forms\Components\Textarea::make('error_output')->disabled()->columnSpanFull()->rows(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website.domain')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trigger_type')
                    ->label('Trigger')
                    ->badge(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Bắt đầu')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('finished_at')
                    ->label('Kết thúc')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('website_id')
                    ->relationship('website', 'domain')
                    ->label('Website'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListDeploymentLogs::route('/'),
            'view' => Pages\ViewDeploymentLog::route('/{record}'),
        ];
    }
}
