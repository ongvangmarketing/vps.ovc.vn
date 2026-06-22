<x-filament-widgets::widget>
    <x-filament::section heading="Server" icon="heroicon-o-server">
        <div class="space-y-6">
            @if($server)
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">CPU</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $server->cpu_usage ?? '0%' }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $server->cpu_usage ?? '0%' }}"></div>
                </div>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">RAM</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $server->ram_usage ?? '0%' }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-success-600 h-2 rounded-full" style="width: {{ $server->ram_usage ?? '0%' }}"></div>
                </div>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Disk</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $server->disk_usage ?? '0%' }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                    <div class="bg-info-600 h-2 rounded-full" style="width: {{ str_replace('GB', '', $server->disk_usage) ?? '0' }}%"></div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('filament.admin.resources.servers.index') }}" class="text-sm text-primary-600 hover:underline">Xem chi tiết server &rarr;</a>
            </div>
            @else
            <div class="text-center text-gray-500 py-4">
                Chưa có dữ liệu server.
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
