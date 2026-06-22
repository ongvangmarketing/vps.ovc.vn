<?php

namespace App\Filament\Widgets;

use App\Models\Website;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentDeploymentsTable extends BaseWidget
{
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 2,
    ];
    
    protected static ?string $heading = 'Website gần đây';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Website::query()->latest('updated_at')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Website $record): string => $record->domain),
                Tables\Columns\TextColumn::make('root_path')
                    ->label('Folder VPS')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),
                Tables\Columns\IconColumn::make('auto_deploy')
                    ->label('Auto Deploy')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_deployed_at')
                    ->label('Lần deploy cuối')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                Tables\Actions\Action::make('manage')
                    ->label('Quản lý')
                    ->url(fn (Website $record): string => route('filament.admin.resources.websites.edit', $record))
                    ->button()
                    ->outlined(),
            ]);
    }
}
