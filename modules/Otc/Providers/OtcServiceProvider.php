<?php

namespace Modules\Otc\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Core\Module\ModuleServiceProvider as ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Otc\Console\CancelExpireOtcTrade;
use Modules\Otc\Listeners\TradeEventListener;
use Modules\Otc\Models\OtcTrade;

class OtcServiceProvider extends ServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Otc';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'otc';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerModelObserver();
//        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
//        $this->registerFactories();
//        $this->registerModelRelations();
        $this->registerCommand();
        $this->registerSchedules();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
//    public function register()
//    {
//    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), 'otc::'
        );
    }

    public function registerModelObserver()
    {
        OtcTrade::observe(TradeEventListener::class);
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/' . $this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
        }
    }

    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if (!app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path($this->moduleName, 'Database/factories'));
        }
    }

    /**
     * Register relations to specific model
     */
    public function registerModelRelations()
    {
//        \App\Models\User::registerRelations([
//            // NOTE: The key must unique!!!
//            'name' => function($model) {
//                return $model->hasOne(Model::class);
//            }
//        ]);
    }

    /**
     * Register Command
     */
    public function registerCommand()
    {
        $this->commands([
            CancelExpireOtcTrade::class
        ]);
    }

    /**
     * Register the service command.
     */
    public function registerSchedules()
    {
        $this->addSchedules(function ($schedule) {
            /** @var Schedule $schedule */

            $schedule->command(CancelExpireOtcTrade::class)
                ->withoutOverlapping()
                ->hourly();
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
