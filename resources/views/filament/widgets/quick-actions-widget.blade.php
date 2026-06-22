<x-filament-widgets::widget>
    <x-filament::section heading="Thao tác nhanh" icon="heroicon-o-bolt">
        <div class="flex flex-col gap-4">
            <a href="{{ route('filament.admin.resources.websites.create') }}" class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded-lg text-primary-600 dark:text-primary-400">
                        <x-heroicon-o-globe-alt class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Thêm Website</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Deploy website mới</p>
                    </div>
                </div>
                <x-heroicon-m-chevron-right class="w-5 h-5 text-gray-400" />
            </a>
            
            <a href="{{ route('filament.admin.resources.databases.create') }}" class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-info-100 dark:bg-info-900 rounded-lg text-info-600 dark:text-info-400">
                        <x-heroicon-o-circle-stack class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Tạo Database</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tạo database MySQL</p>
                    </div>
                </div>
                <x-heroicon-m-chevron-right class="w-5 h-5 text-gray-400" />
            </a>

            <a href="{{ route('filament.admin.resources.deployment-logs.index') }}" class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-warning-100 dark:bg-warning-900 rounded-lg text-warning-600 dark:text-warning-400">
                        <x-heroicon-o-document-text class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Xem Logs</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Xem lịch sử deploy</p>
                    </div>
                </div>
                <x-heroicon-m-chevron-right class="w-5 h-5 text-gray-400" />
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
