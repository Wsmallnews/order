<?php

namespace Wsmallnews\Order;

use Filament\Support\Assets\Asset;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\Order\Commands\OrderInstallCommand;
use Wsmallnews\Order\Livewire\Components\Confirm;
use Wsmallnews\Order\Support\Utils;
use Wsmallnews\Support\Features\Modules\Module;
use Wsmallnews\Support\Features\Modules\ModuleRegistry;

class OrderServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-order';

    public static string $viewNamespace = 'sn-order';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands());

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
        // 模块身份登记（ModuleRegistry 单一事实源：类反查/存在性校验/插件实例）
        ModuleRegistry::register(new Module(
            id: static::$name,
            namespace: 'Wsmallnews\\Order',
            plugin: OrderPlugin::class,
        ));
    }

    public function packageBooted(): void
    {
        // 注册模型别名
        Relation::enforceMorphMap([
            'sn_order' => Utils::getOrderModel(),
            'sn_order_item' => Utils::getOrderItemModel(),
        ]);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                if (str_starts_with($file->getFilename(), '.')) {
                    continue;
                }

                $this->publishes([
                    $file->getRealPath() => base_path("stubs/order/{$file->getFilename()}"),
                ], 'order-stubs');
            }
        }

        // 注册 livewire 命名空间（自动发现 src/Livewire/ 下的组件）
        Livewire::addNamespace(
            namespace: 'sn-order',
            classNamespace: 'Wsmallnews\\Order\\Livewire'
        );

        // 兼容旧别名（shop 等调用方的历史引用）
        Livewire::component('sn-order-confirm', Confirm::class);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'wsmallnews/order';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            OrderInstallCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_sn_orders_table',
            'create_sn_order_items_table',
            'create_sn_order_actions_table',
        ];
    }
}
