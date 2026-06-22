<div class="space-y-8">
    <!-- Thông tin website -->
    <div>
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-information-circle class="w-5 h-5 text-primary-600"/>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Thông tin website</h3>
        </div>
        
        <div class="grid grid-cols-3 gap-6 bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700">
            <!-- Col 1 -->
            <div class="space-y-4">
                <div>
                    <div class="text-xs text-gray-500 mb-1">Tên website</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $get('name') ?? '...' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Domain</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $get('domain') ?: ($get('subdomain') ? $get('subdomain').'.'.$get('base_domain') : '...') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Loại project</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $get('type') ?? '...' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Môi trường</div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $get('environment') ?? 'Production' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Auto Deploy</div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-50 text-success-700 border border-success-200">
                        Bật
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">SSL (Let's Encrypt)</div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-50 text-success-700 border border-success-200">
                        Bật
                    </div>
                </div>
            </div>

            <!-- Col 2 -->
            <div class="space-y-4">
                <div>
                    <div class="text-xs text-gray-500 mb-1">Nguồn</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-1">
                        <x-heroicon-o-globe-alt class="w-4 h-4 text-gray-400"/>
                        {{ $get('source_type') === 'github' ? 'GitHub' : ($get('source_type') === 'git' ? 'Git URL' : 'Upload') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Repository</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $get('repo_url') }}">{{ $get('repo_url') ?? '...' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Branch</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $get('branch') ?? 'main' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">VPS Folder</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        @php
                            $folder = '';
                            if ($get('vps_folder_id')) {
                                $f = \App\Models\VpsFolder::find($get('vps_folder_id'));
                                $folder = $f ? $f->slug : '';
                            } elseif ($get('new_folder_slug')) {
                                $folder = $get('new_folder_slug');
                            }
                        @endphp
                        /var/www/sites/{{ $folder }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Root Path</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">/var/www/sites/{{ $folder }}{{ $get('deploy_path') && $get('deploy_path') !== '/' ? '/' . trim($get('deploy_path'), '/') : '' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Public Path</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">/var/www/sites/{{ $folder }}{{ $get('deploy_path') && $get('deploy_path') !== '/' ? '/' . trim($get('deploy_path'), '/') : '' }}/{{ trim($get('public_dir') ?? 'public', '/') }}</div>
                </div>
            </div>

            <!-- Col 3 -->
            <div class="space-y-4">
                <div>
                    <div class="text-xs text-gray-500 mb-1">PHP Version</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $get('php_version') ?? '8.3' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Node Version</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $get('node_version') ?? '20.x' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Build Command</div>
                    <div class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-800 dark:text-gray-200 mt-1 inline-block">
                        {{ $get('build_command') ?: 'npm run build' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Start Command</div>
                    <div class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-800 dark:text-gray-200 mt-1 inline-block">
                        {{ $get('start_command') ?: 'php artisan serve' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Nginx Config</div>
                    <div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-50 text-success-700 border border-success-200">
                        {{ $get('auto_nginx_config') ? 'Tự động tạo' : 'Thủ công' }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 mb-1">Deploy Command</div>
                    <div class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono text-gray-800 dark:text-gray-200 mt-1 inline-block">
                        {{ $get('auto_install_deps') ? 'npm install && ' : '' }}{{ $get('build_command') ?: 'npm run build' }}{{ $get('auto_migrate') ? ' && php artisan migrate' : '' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quy trình triển khai -->
    <div>
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-cog class="w-5 h-5 text-primary-600"/>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Quy trình triển khai</h3>
        </div>
        
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800">
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                <!-- Bước 1 -->
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-folder class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">01</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Tạo thư mục trên VPS</div>
                            <div class="text-xs text-gray-500">/var/www/sites/{{ $folder }}/{{ $get('subdomain') ? $get('subdomain').'.'.$get('base_domain') : '...' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>
                
                <!-- Bước 2 -->
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-cloud-arrow-down class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">02</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Clone repository từ GitHub</div>
                            <div class="text-xs text-gray-500">{{ $get('repo_url') ?? '...' }} (branch: {{ $get('branch') ?? 'main' }})</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>

                <!-- Bước 3 -->
                @if($get('auto_install_deps'))
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-cube class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">03</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Cài đặt dependencies</div>
                            <div class="text-xs text-gray-500">Composer install & NPM install</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>
                @endif

                <!-- Bước 4 -->
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-command-line class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">04</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Build source</div>
                            <div class="text-xs text-gray-500">{{ $get('build_command') ?: 'npm run build' }}</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>

                <!-- Bước 5 -->
                @if($get('auto_migrate'))
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-circle-stack class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">05</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Chạy migrate (nếu có)</div>
                            <div class="text-xs text-gray-500">php artisan migrate --force</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>
                @endif

                <!-- Bước 6 -->
                @if($get('auto_nginx_config'))
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-server class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">06</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Tạo cấu hình Nginx</div>
                            <div class="text-xs text-gray-500">Tự động tạo và enable site</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>
                @endif

                <!-- Bước 7 -->
                @if($get('auto_nginx_config') && $get('domain') && $get('auto_attach_domain'))
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-lock-closed class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">07</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Cài SSL (Let's Encrypt)</div>
                            <div class="text-xs text-gray-500">Cấp SSL miễn phí cho domain chính</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>
                @endif

                <!-- Bước 8 -->
                @if($get('auto_reload_nginx'))
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-arrow-path class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">08</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Reload Nginx</div>
                            <div class="text-xs text-gray-500">Reload cấu hình để website hoạt động</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>
                @endif

                <!-- Bước 9 -->
                <li class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center font-bold text-sm">
                            <x-heroicon-o-check-badge class="w-4 h-4"/>
                        </div>
                        <div class="font-bold text-gray-400 text-lg">09</div>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 dark:text-white">Kiểm tra website online</div>
                            <div class="text-xs text-gray-500">Kiểm tra HTTP/HTTPS và trạng thái website</div>
                        </div>
                    </div>
                    <div class="flex items-center text-success-600 text-sm font-medium gap-1">
                        <x-heroicon-o-check-circle class="w-5 h-5"/> Sẵn sàng
                    </div>
                </li>

            </ul>
        </div>
    </div>
</div>
