<?php

namespace Modules\Coinv2\Providers;

use Modules\Coinv2\Jobs\CheckTradeJobV2;
use Modules\Coinv2\Jobs\SyncCoinPriceJobV2;
use Modules\Coinv2\Jobs\SyncTradeStateJobV2;
use Modules\Coinv2\Jobs\TokenioNoticeProcessJobV2;
use Modules\Coinv2\Services\TokenioNoticeService;
use Modules\Core\Module\Traits\HasSchedule;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Coin\Components\Exchange\Manager;
use Modules\Coinv2\Components\TokenIO\TokenIO;

class Coinv2ServiceProvider extends ServiceProvider
{
    use HasSchedule;
    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Coinv2';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'coinv2';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {

        $this->registerSchedules();

//        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
//        $this->registerFactories();
//        $this->registerModelRelations();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        $this->app->singleton(TokenIO::class, function () {
            $client = new TokenIO(
                config('coinv2::tokenioV2.key'),
                config('coinv2::tokenioV2.secret')
            );
            if ($host = config('coinv2::tokenioV2.host')) {
                $client->setHost($host);
            }
            return $client;
        });

        $this->app->singleton(Manager::class);


    }


    public function registerSchedules()
    {
        $this->addSchedules(function($schedule) {
            /** @var Schedule $schedule */

//            $schedule->job(new SyncCoinPriceJobV2())
//                ->withoutOverlapping()
//                ->everyMinute();

            $schedule->job(new CheckTradeJobV2())
                ->withoutOverlapping()
                ->everyTwoMinutes();

//            $schedule->job(new SyncTradeStateJobV2())
//                ->withoutOverlapping()
//                ->everyFiveMinutes();

            //轮询处理通知消息
            $schedule->job(new TokenioNoticeProcessJobV2())
                ->withoutOverlapping()
                ->everyTwoMinutes();




        });
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
            module_path($this->moduleName, 'Config/config.php'), 'coinv2::'
        );
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
        if (! app()->environment('production') && $this->app->runningInConsole()) {
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
