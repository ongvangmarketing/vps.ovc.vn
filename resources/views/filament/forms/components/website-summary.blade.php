<div class="space-y-4 text-sm">
    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-globe-alt class="w-4 h-4"/> Nguồn</span>
        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $get('source_type') ?? '...' }}</span>
    </div>
    
    @if(in_array($get('source_type'), ['github', 'git']))
    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-code-bracket class="w-4 h-4"/> Repository</span>
        <span class="font-semibold text-gray-900 dark:text-gray-100 truncate max-w-[150px]" title="{{ $get('repo_url') }}">{{ $get('repo_url') ?? '...' }}</span>
    </div>
    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-tag class="w-4 h-4"/> Branch</span>
        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $get('branch') ?? 'main' }}</span>
    </div>
    @endif

    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-link class="w-4 h-4"/> Domain</span>
        <span class="font-semibold text-primary-600">{{ $get('domain') ?? '...' }}</span>
    </div>

    @php
        $vpsFolderSlug = '';
        if ($get('vps_folder_id')) {
            $folder = \App\Models\VpsFolder::find($get('vps_folder_id'));
            $vpsFolderSlug = $folder ? $folder->slug : '';
        } elseif ($get('new_folder_slug')) {
            $vpsFolderSlug = $get('new_folder_slug');
        }
        $vpsFolderPath = $vpsFolderSlug ? '/var/www/sites/' . $vpsFolderSlug : '/var/www/sites';
        $rootPath = $vpsFolderPath . '/' . ($get('domain') ?? '');
    @endphp

    <div class="flex flex-col py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2 mb-1"><x-heroicon-o-folder class="w-4 h-4"/> VPS Folder</span>
        <span class="text-xs text-gray-900 dark:text-gray-300 break-all">{{ $vpsFolderPath }}</span>
    </div>
    
    <div class="flex flex-col py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2 mb-1"><x-heroicon-o-folder-open class="w-4 h-4"/> Root Path</span>
        <span class="text-xs text-gray-900 dark:text-gray-300 break-all">{{ $rootPath }}</span>
    </div>

    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-cube class="w-4 h-4"/> Loại project</span>
        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $get('type') ?? '...' }}</span>
    </div>

    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-command-line class="w-4 h-4"/> PHP / Node</span>
        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $get('php_version') }} / {{ $get('node_version') }}</span>
    </div>

    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
        <span class="text-gray-500 font-medium flex items-center gap-2"><x-heroicon-o-shield-check class="w-4 h-4"/> SSL</span>
        @if($get('ssl_enabled'))
            <span class="px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800">Bật</span>
        @else
            <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Tắt</span>
        @endif
    
    <div class="mt-6 p-4 bg-primary-50 dark:bg-primary-900/30 rounded-xl border border-primary-100 dark:border-primary-800">
        <div class="flex items-start gap-3">
            <x-heroicon-o-shield-check class="w-5 h-5 text-primary-600 dark:text-primary-400 mt-0.5 shrink-0"/>
            <div>
                <div class="text-sm font-semibold text-primary-900 dark:text-primary-300">Bảo mật</div>
                <div class="text-xs text-primary-700 dark:text-primary-400 mt-1">Webhook sẽ được tạo sau khi xuất bản để GitHub tự động deploy mỗi khi có push code.</div>
            </div>
        </div>
    </div>
</div>
</div>
