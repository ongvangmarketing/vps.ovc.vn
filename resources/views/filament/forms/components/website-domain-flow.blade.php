<div class="space-y-4">
    <div class="flex items-center justify-center bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
        <div class="text-center px-6">
            <div class="text-xs text-primary-600 font-medium mb-1">Subdomain Hệ Thống</div>
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $get('subdomain') ? $get('subdomain').'.'.$get('base_domain') : '...' }}
            </div>
            <div class="text-xs text-gray-500 mt-1">(Dùng để truy cập)</div>
        </div>
        <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400 mx-4"/>
        <div class="text-center px-6 py-3 bg-success-50 dark:bg-success-900/30 rounded border border-success-100 dark:border-success-800">
            <div class="text-xs text-success-600 font-medium mb-1">Mã Nguồn</div>
            <div class="text-lg font-semibold text-success-900 dark:text-success-100">{{ $get('type') ? \Illuminate\Support\Str::upper($get('type')) : 'Website' }}</div>
        </div>
    </div>
    <ul class="text-xs text-gray-500 space-y-2 list-disc pl-4">
        <li>Ngay sau khi Deploy, Website sẽ chạy trên Subdomain này để bạn kiểm thử.</li>
        <li>Bạn có thể gắn thêm Tên miền riêng (Domain chính) bất cứ lúc nào trong phần Cài đặt Website sau.</li>
    </ul>
</div>
