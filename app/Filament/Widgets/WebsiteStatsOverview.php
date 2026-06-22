<?php

namespace App\Filament\Widgets;

use App\Models\Database;
use App\Models\Website;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsiteStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';
    
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 5;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Tổng Website', Website::count())
                ->description('Tổng số website trong hệ thống')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('primary'),
            
            Stat::make('Trực tuyến', Website::where('status', 'online')->count())
                ->description('Website đang hoạt động')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('Đang triển khai', Website::where('status', 'deploying')->count())
                ->description('Đang chạy deploy')
                ->descriptionIcon('heroicon-m-rocket-launch')
                ->color('warning'),
                
            Stat::make('Gặp lỗi', Website::where('status', 'error')->count())
                ->description('Cần xử lý')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
                
            Stat::make('Database', Database::count())
                ->description('Tổng database')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('info'),
        ];
    }
}
