<?php

namespace App\Filament\Resources;

use App\Enums\WebsiteStatus;
use App\Enums\WebsiteType;
use App\Filament\Resources\WebsiteResource\Pages;
use App\Models\Website;
use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WebsiteResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    
    protected static ?string $navigationLabel = 'Deploy Website';
    
    protected static ?string $modelLabel = 'Website';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Chọn nguồn')
                        ->description('Chọn nguồn code website')
                        ->schema([
                            Forms\Components\Radio::make('source_type')
                                ->label('Chọn cách bạn muốn đưa code website lên hệ thống')
                                ->options([
                                    'upload' => 'Upload ZIP / HTML',
                                    'github' => 'GitHub',
                                    'git' => 'Git URL',
                                    'html' => 'Dán HTML',
                                ])
                                ->descriptions([
                                    'upload' => 'Upload file .zip, .html hoặc .htm từ máy tính của bạn',
                                    'github' => 'Kết nối repository từ GitHub để tự động deploy',
                                    'git' => 'Nhập link Git repository để clone code',
                                    'html' => 'Dán trực tiếp mã HTML để xuất bản nhanh',
                                ])
                                ->columns(4)
                                ->required()
                                ->reactive(),
                            
                            Forms\Components\FileUpload::make('zip_file')
                                ->label('Kéo thả file vào đây hoặc click để chọn file')
                                ->visible(fn (\Filament\Forms\Get $get) => $get('source_type') === 'upload')
                                ->required(fn (\Filament\Forms\Get $get) => $get('source_type') === 'upload'),
                                
                            Forms\Components\Grid::make(3)
                                ->visible(fn (\Filament\Forms\Get $get) => in_array($get('source_type'), ['github', 'git']))
                                ->schema([
                                    Forms\Components\Hidden::make('repo_url'),
                                    
                                    Forms\Components\ViewField::make('github_auth')
                                        ->view('filament.forms.components.github-auth')
                                        ->visible(fn (\Filament\Forms\Get $get) => $get('source_type') === 'github' && !auth()->user()?->github_token)
                                        ->columnSpan(1),
                                        
                                    Forms\Components\Select::make('github_repo_url')
                                        ->label('Chọn Repository')
                                        ->placeholder('Tìm kiếm repository...')
                                        ->searchable()
                                        ->preload()
                                        ->getSearchResultsUsing(function (string $search) {
                                            $token = auth()->user()?->github_token;
                                            if (!$token) return [];
                                            try {
                                                $response = \Illuminate\Support\Facades\Http::withToken($token)
                                                    ->timeout(5)
                                                    ->get('https://api.github.com/user/repos', [
                                                        'sort' => 'updated',
                                                        'per_page' => 50,
                                                    ]);
                                                if ($response->successful()) {
                                                    $repos = collect($response->json());
                                                    if ($search) {
                                                        $repos = $repos->filter(fn($r) => str_contains(strtolower($r['full_name']), strtolower($search)));
                                                    }
                                                    return $repos->pluck('full_name', 'html_url')->toArray();
                                                }
                                            } catch (\Exception $e) {}
                                            return [];
                                        })
                                        ->getOptionLabelUsing(fn ($value) => str_replace('https://github.com/', '', $value))
                                        ->visible(fn (\Filament\Forms\Get $get) => $get('source_type') === 'github' && auth()->user()?->github_token)
                                        ->required(fn (\Filament\Forms\Get $get) => $get('source_type') === 'github' && auth()->user()?->github_token)
                                        ->live()
                                        ->afterStateUpdated(function (?string $state, Forms\Set $set, \Filament\Forms\Get $get) {
                                            if ($state) {
                                                $set('repo_url', $state);
                                                $path = parse_url($state, PHP_URL_PATH);
                                                if ($path) {
                                                    $parts = explode('/', trim($path, '/'));
                                                    $repoName = end($parts);
                                                    $repoName = str_replace('.git', '', $repoName);
                                                    if ($repoName) {
                                                        if (empty($get('name'))) $set('name', \Illuminate\Support\Str::headline($repoName));
                                                        if (empty($get('subdomain'))) $set('subdomain', \Illuminate\Support\Str::slug($repoName));
                                                    }
                                                    // Auto detect
                                                    $token = auth()->user()?->github_token;
                                                    $user = $parts[count($parts) - 2] ?? null;
                                                    $repo = $parts[count($parts) - 1] ?? null;
                                                    if ($user && $repo && $token) {
                                                        $repo = str_replace('.git', '', $repo);
                                                        try {
                                                            $response = \Illuminate\Support\Facades\Http::withToken($token)->timeout(5)->get("https://api.github.com/repos/{$user}/{$repo}/contents");
                                                            if ($response->successful()) {
                                                                $files = collect($response->json())->pluck('name')->toArray();
                                                                
                                                                $type = 'static';
                                                                if (in_array('artisan', $files) && in_array('composer.json', $files)) $type = 'laravel';
                                                                elseif (in_array('next.config.js', $files) || in_array('next.config.mjs', $files) || in_array('next.config.ts', $files)) $type = 'next';
                                                                elseif (in_array('nuxt.config.js', $files) || in_array('nuxt.config.ts', $files) || in_array('vite.config.js', $files) || in_array('vue.config.js', $files)) $type = 'vue';
                                                                elseif (in_array('package.json', $files)) $type = 'node';
                                                                
                                                                $set('type', $type);
                                                                
                                                                // Apply defaults based on detected type
                                                                if ($type === 'laravel') {
                                                                    $set('php_version', '8.3'); $set('node_version', '20.x'); $set('build_command', 'npm run build'); $set('start_command', ''); $set('public_dir', 'public'); $set('auto_install_deps', true); $set('auto_migrate', true);
                                                                } elseif ($type === 'next') {
                                                                    $set('php_version', null); $set('node_version', '20.x'); $set('build_command', 'npm run build'); $set('start_command', 'npm run start'); $set('public_dir', 'public'); $set('auto_install_deps', true); $set('auto_migrate', false);
                                                                } elseif ($type === 'vue') {
                                                                    $set('php_version', null); $set('node_version', '20.x'); $set('build_command', 'npm run build'); $set('start_command', ''); $set('public_dir', 'dist'); $set('auto_install_deps', true); $set('auto_migrate', false);
                                                                } elseif ($type === 'node') {
                                                                    $set('php_version', null); $set('node_version', '20.x'); $set('build_command', ''); $set('start_command', 'npm start'); $set('public_dir', 'public'); $set('auto_install_deps', true); $set('auto_migrate', false);
                                                                } elseif ($type === 'static') {
                                                                    $set('php_version', null); $set('node_version', null); $set('build_command', ''); $set('start_command', ''); $set('public_dir', ''); $set('auto_install_deps', false); $set('auto_migrate', false);
                                                                }
                                                            }
                                                        } catch (\Exception $e) {}
                                                    }
                                                }
                                            }
                                        }),
                                        
                                    Forms\Components\TextInput::make('git_repo_url')
                                        ->label('Git Repository URL')
                                        ->placeholder('https://gitlab.com/username/repo.git')
                                        ->visible(fn (\Filament\Forms\Get $get) => $get('source_type') === 'git')
                                        ->required(fn (\Filament\Forms\Get $get) => $get('source_type') === 'git')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function (?string $state, Forms\Set $set, \Filament\Forms\Get $get) {
                                            if ($state) {
                                                $set('repo_url', $state);
                                                $path = parse_url($state, PHP_URL_PATH);
                                                if ($path) {
                                                    $parts = explode('/', trim($path, '/'));
                                                    $repoName = end($parts);
                                                    $repoName = str_replace('.git', '', $repoName);
                                                    if ($repoName) {
                                                        if (empty($get('name'))) $set('name', \Illuminate\Support\Str::headline($repoName));
                                                        if (empty($get('subdomain'))) $set('subdomain', \Illuminate\Support\Str::slug($repoName));
                                                    }
                                                }
                                            }
                                        }),
                                    Forms\Components\TextInput::make('branch')->label('Branch')->default('main')->live(),
                                    Forms\Components\TextInput::make('deploy_path')->label('Deploy từ thư mục (tùy chọn)')->default('/')->placeholder('VD: /backend'),
                                ]),
                                
                            Forms\Components\Textarea::make('raw_html')
                                ->label('Dán trực tiếp mã HTML')
                                ->rows(10)
                                ->visible(fn (\Filament\Forms\Get $get) => $get('source_type') === 'html')
                                ->required(fn (\Filament\Forms\Get $get) => $get('source_type') === 'html'),
                                
                            Forms\Components\Section::make('Gợi ý')
                                ->description('Nếu bạn chỉ có file HTML đơn giản, hãy dùng chức năng "Dán HTML" để xuất bản nhanh nhất. Nếu website có nhiều file (CSS, JS, ảnh...), hãy nén thành file .zip để upload.')
                                ->icon('heroicon-o-information-circle')
                        ]),
                    Wizard\Step::make('Chọn thư mục VPS')
                        ->description('Chọn nơi lưu trữ trên VPS')
                        ->schema([
                            Forms\Components\Grid::make(1)
                                ->schema([
                                    Forms\Components\Section::make('1. Chọn thư mục có sẵn')
                                        ->description('Tìm và chọn nhóm thư mục đã được tạo trên hệ thống.')
                                        ->schema([
                                            Forms\Components\Select::make('vps_folder_id')
                                                ->label('')
                                                ->placeholder('Gõ để tìm kiếm thư mục...')
                                                ->options(\App\Models\VpsFolder::all()->pluck('name', 'id'))
                                                ->searchable()
                                                ->preload()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    if ($state) {
                                                        $set('new_folder_name', null);
                                                        $set('new_folder_slug', null);
                                                        $set('new_folder_type', null);
                                                        $set('new_folder_description', null);
                                                    }
                                                }),
                                        ]),
                                        
                                    Forms\Components\Section::make('2. Hoặc tạo nhóm thư mục mới (Thủ công)')
                                        ->description('Nếu bạn muốn nhóm các website vào một thư mục mới.')
                                        ->schema([
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\TextInput::make('new_folder_name')
                                                    ->label('Tên nhóm thư mục')
                                                    ->placeholder('VD: Khách hàng VIP')
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, Forms\Set $set, \Filament\Forms\Get $get) {
                                                        if ($state) {
                                                            $set('vps_folder_id', null);
                                                            if (!$get('new_folder_slug')) {
                                                                $set('new_folder_slug', \Illuminate\Support\Str::slug($state));
                                                            }
                                                        }
                                                    }),
                                                Forms\Components\TextInput::make('new_folder_slug')
                                                    ->label('Slug thư mục')
                                                    ->placeholder('khach-hang-vip')
                                                    ->live(onBlur: true),
                                            ]),
                                            
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\Select::make('new_folder_type')
                                                    ->label('Loại thư mục')
                                                    ->options([
                                                        'system' => 'Hệ thống (System)',
                                                        'client' => 'Khách hàng (Client)',
                                                        'project' => 'Dự án (Project)',
                                                        'other' => 'Khác'
                                                    ]),
                                                Forms\Components\TextInput::make('new_folder_description')
                                                    ->label('Mô tả (tùy chọn)'),
                                            ]),

                                            Forms\Components\Placeholder::make('preview_folder_path')
                                                ->label('Đường dẫn sẽ được tạo')
                                                ->content(fn (\Filament\Forms\Get $get) => $get('new_folder_slug') ? new \Illuminate\Support\HtmlString('<code class="text-primary-600 bg-primary-50 px-2 py-1 rounded">/var/www/sites/' . $get('new_folder_slug') . '</code>') : '...'),
                                            Forms\Components\Section::make('Lưu ý')
                                                ->description('Website sẽ được lưu trong thư mục con theo domain. Ví dụ: /var/www/sites/ongvang-system/ongvang.com.vn')
                                                ->icon('heroicon-o-information-circle')
                                        ]),
                                ]),
                        ]),
                    Wizard\Step::make('Cấu hình website')
                        ->description('Thiết lập thông tin')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Grid::make(1)
                                        ->columnSpan(2)
                                        ->schema([
                                            Forms\Components\Section::make('Thông tin cơ bản')
                                                ->columns(4)
                                                ->schema([
                                                    Forms\Components\TextInput::make('name')->label('Tên website')->required(),
                                                    Forms\Components\Select::make('type')->label('Loại project')->options(\App\Enums\WebsiteType::class)->required()->live()
                                                        ->afterStateUpdated(function (?string $state, Forms\Set $set) {
                                                            if ($state === 'laravel') {
                                                                $set('php_version', '8.3');
                                                                $set('node_version', '20.x');
                                                                $set('build_command', 'npm run build');
                                                                $set('start_command', '');
                                                                $set('public_dir', 'public');
                                                                $set('auto_install_deps', true);
                                                                $set('auto_migrate', true);
                                                            } elseif ($state === 'next') {
                                                                $set('php_version', null);
                                                                $set('node_version', '20.x');
                                                                $set('build_command', 'npm run build');
                                                                $set('start_command', 'npm run start');
                                                                $set('public_dir', 'public');
                                                                $set('auto_install_deps', true);
                                                                $set('auto_migrate', false);
                                                            } elseif ($state === 'vue') {
                                                                $set('php_version', null);
                                                                $set('node_version', '20.x');
                                                                $set('build_command', 'npm run build');
                                                                $set('start_command', '');
                                                                $set('public_dir', 'dist');
                                                                $set('auto_install_deps', true);
                                                                $set('auto_migrate', false);
                                                            } elseif ($state === 'node') {
                                                                $set('php_version', null);
                                                                $set('node_version', '20.x');
                                                                $set('build_command', '');
                                                                $set('start_command', 'npm start');
                                                                $set('public_dir', 'public');
                                                                $set('auto_install_deps', true);
                                                                $set('auto_migrate', false);
                                                            } elseif ($state === 'static') {
                                                                $set('php_version', null);
                                                                $set('node_version', null);
                                                                $set('build_command', '');
                                                                $set('start_command', '');
                                                                $set('public_dir', '');
                                                                $set('auto_install_deps', false);
                                                                $set('auto_migrate', false);
                                                            }
                                                        }),
                                                    Forms\Components\Select::make('environment')->label('Môi trường')->options(['Production'=>'Production','Staging'=>'Staging','Testing'=>'Testing'])->default('Production')->live(),
                                                    Forms\Components\Hidden::make('php_version')->default('8.3'),
                                                    Forms\Components\Hidden::make('node_version')->default('20.x'),
                                                    Forms\Components\Hidden::make('build_command')->default('npm run build'),
                                                    Forms\Components\Hidden::make('start_command'),
                                                    Forms\Components\Hidden::make('public_dir')->default('public'),
                                                    Forms\Components\Hidden::make('auto_install_deps')->default(true),
                                                    Forms\Components\Hidden::make('auto_migrate')->default(true),
                                                ]),

                                            Forms\Components\Section::make('Tùy chọn nâng cao')
                                                ->columns(4)
                                                ->schema([
                                                    Forms\Components\Toggle::make('auto_deploy')->label('Auto Deploy khi GitHub push')->default(true)->live(),
                                                    Forms\Components\Toggle::make('auto_nginx_config')->label('Tự tạo Nginx config')->default(true)->live(),
                                                    Forms\Components\Toggle::make('ssl_enabled')->label('Bật SSL (Let\'s Encrypt)')->default(true)->live(),
                                                    Forms\Components\Toggle::make('auto_reload_nginx')->label('Reload Nginx sau deploy')->default(true)->live(),
                                                ]),
                                        ]),
                                    Forms\Components\Section::make('Tóm tắt cấu hình')
                                        ->columnSpan(1)
                                        ->schema([
                                            Forms\Components\Placeholder::make('summary')
                                                ->content(fn (\Filament\Forms\Get $get) => view('filament.forms.components.website-summary', ['get' => $get]))
                                                ->label(''),
                                        ])
                                ]),
                        ]),
                    Wizard\Step::make('Tên miền')
                        ->description('Cấu hình tên miền')
                        ->schema([
                            Forms\Components\Grid::make(3)
                                ->schema([
                                    Forms\Components\Grid::make(1)
                                        ->columnSpan(2)
                                        ->schema([
                                            Forms\Components\Section::make('Cấu hình Subdomain')
                                                ->description('Website của bạn sẽ được truy cập thông qua subdomain này.')
                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->schema([
                                                            Forms\Components\TextInput::make('subdomain')
                                                                ->label('Subdomain')
                                                                ->required()
                                                                ->live(onBlur: true),
                                                            Forms\Components\Select::make('base_domain')
                                                                ->label('Base Domain')
                                                                ->options(['vps.ovc.vn' => '.vps.ovc.vn'])
                                                                ->default('vps.ovc.vn')
                                                                ->required()
                                                                ->live(),
                                                        ]),
                                                    Forms\Components\Placeholder::make('preview_subdomain')
                                                        ->label('')
                                                        ->content(function (\Filament\Forms\Get $get) {
                                                            $sub = $get('subdomain');
                                                            $base = $get('base_domain');
                                                            if ($sub && $base) {
                                                                return new \Illuminate\Support\HtmlString('Sau khi deploy, website truy cập tại: <br><a href="https://' . $sub . '.' . $base . '" target="_blank" class="text-primary-600 font-medium">https://' . $sub . '.' . $base . ' <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 inline"/></a>');
                                                            }
                                                            return '';
                                                        })
                                                ]),
                                                
                                            Forms\Components\Hidden::make('domain'),
                                            Forms\Components\Hidden::make('auto_attach_domain')->default(false),
                                            Forms\Components\Hidden::make('redirect_subdomain')->default(false),
                                        ]),
                                        
                                    Forms\Components\Grid::make(1)
                                        ->columnSpan(1)
                                        ->schema([
                                            Forms\Components\Section::make('Thông tin kết nối VPS')
                                                ->schema([
                                                    Forms\Components\TextInput::make('vps_ip')
                                                        ->label('IP VPS')
                                                        ->default(env('SERVER_IP', '103.200.22.10'))
                                                        ->disabled()
                                                        ->extraInputAttributes(['readonly' => true]),
                                                    Forms\Components\TextInput::make('preview_root_path')
                                                        ->label('VPS Folder')
                                                        ->default(function (\Filament\Forms\Get $get) {
                                                            $vpsFolderSlug = '';
                                                            if ($get('vps_folder_id')) {
                                                                $folder = \App\Models\VpsFolder::find($get('vps_folder_id'));
                                                                $vpsFolderSlug = $folder ? $folder->slug : '';
                                                            } elseif ($get('new_folder_slug')) {
                                                                $vpsFolderSlug = $get('new_folder_slug');
                                                            }
                                                            return $vpsFolderSlug ? '/var/www/sites/' . $vpsFolderSlug : '/var/www/sites';
                                                        })
                                                        ->disabled()
                                                ]),
                                            Forms\Components\Section::make('Cách hoạt động')
                                                ->schema([
                                                    Forms\Components\Placeholder::make('domain_flow')
                                                        ->content(fn (\Filament\Forms\Get $get) => view('filament.forms.components.website-domain-flow', ['get' => $get]))
                                                        ->label(''),
                                                ])
                                        ])
                                ]),
                        ]),
                    Wizard\Step::make('Xuất bản')
                        ->description('Xem lại và xuất bản')
                        ->schema([
                            Forms\Components\Placeholder::make('review')
                                ->content(fn (\Filament\Forms\Get $get) => view('filament.forms.components.website-publish-review', ['get' => $get]))
                                ->label('')
                        ]),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Website $record): string => $record->domain),
                Tables\Columns\TextColumn::make('root_path')
                    ->label('Folder VPS'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge(),
                Tables\Columns\IconColumn::make('auto_deploy')
                    ->label('Auto Deploy')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_deployed_at')
                    ->label('Lần deploy cuối')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('deploy')
                        ->label('Deploy Now')
                        ->icon('heroicon-o-rocket-launch')
                        ->color('primary')
                        ->action(function (Website $record) {
                            // dispatch job
                            \Illuminate\Support\Facades\Artisan::call('vibe:deploy', ['website_id' => $record->id]);
                            \Filament\Notifications\Notification::make()->title('Đã lên lịch deploy')->success()->send();
                        }),
                    Tables\Actions\Action::make('ssl')
                        ->label('Install SSL')
                        ->icon('heroicon-o-lock-closed')
                        ->action(function (Website $record) {
                            \Illuminate\Support\Facades\Artisan::call('vibe:ssl', ['website_id' => $record->id]);
                            \Filament\Notifications\Notification::make()->title('Đang cài SSL')->success()->send();
                        }),
                    Tables\Actions\Action::make('reload_nginx')
                        ->label('Reload Nginx')
                        ->icon('heroicon-o-arrow-path')
                        ->action(function () {
                            // Run nginx reload
                            \Filament\Notifications\Notification::make()->title('Đã reload Nginx')->success()->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ])
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
            'index' => Pages\ListWebsites::route('/'),
            'create' => Pages\CreateWebsite::route('/create'),
            'edit' => Pages\EditWebsite::route('/{record}/edit'),
        ];
    }
}
