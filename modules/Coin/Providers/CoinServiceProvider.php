<?php

namespace Modules\Coin\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Coin\Jobs\CoinBalanceTotalCheckNotificationJob;
use Modules\Coin\Jobs\SyncTradeStateJob;
use Modules\Coin\Jobs\SystemWalletBalanceNotificationJob;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Jobs\CheckTradeJob;
use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coin\Jobs\SyncCoinPriceJob;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Components\Exchange\Manager;
use Modules\Coin\Listeners\TradeEventListener;
use Modules\Coin\Listeners\RechargeEventListener;
use Modules\Coin\Listeners\WithdrawEventListener;
use Modules\Core\Module\ModuleServiceProvider as ServiceProvider;

class CoinServiceProvider extends ServiceProvider
{

    /**
     * @var string $moduleName
     */
    protected $moduleName = 'Coin';

    /**
     * @var string $moduleNameLower
     */
    protected $moduleNameLower = 'coin';

    /**
     * @var string
     */
    protected $modulePath = __DIR__ . '/..';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerSchedules();
        $this->registerModelObserver();
        $this->registerTranslations();
        $this->registerViews();
//        $this->registerFactories();
//        $this->registerModelRelations();
        $this->loadMigrationsFrom($this->modulePath . '/Database/Migrations');

        $this->app->singleton(TokenIO::class, function () {
            $client = new TokenIO(
                config('coin::tokenio.key'),
                config('coin::tokenio.secret')
            );
            if ($host = config('coin::tokenio.host')) {
                $client->setHost($host);
            }
            return $client;
        });

        $this->app->singleton(Manager::class);
    }

    public function registerModelObserver()
    {
        CoinWithdraw::observe(WithdrawEventListener::class);
        CoinRecharge::observe(RechargeEventListener::class);
        CoinTrade::observe(TradeEventListener::class);
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/coin');

        $sourcePath = module_path('Coin', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath,
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/coin';
        }, \Config::get('view.paths')), [$sourcePath]), 'coin');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('Coin', 'Config/config.php') => config_path('coin.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('Coin', 'Config/config.php'), 'coin::'
        );
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/coin');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'coin');
        } else {
            $this->loadTranslationsFrom(module_path('Coin', 'Resources/lang'), 'coin');
        }
    }

    /**
     * Register relations to specific model
     */
//    public function registerModelRelations()
//    {
//        User::registerRelations([
//            'name' => function($model) {
//                return $model->hasOne(Model::class);
//            }
//        ]);
//    }
    /**
     * Register an additional directory of factories.
     *
     * @return void
     */
    public function registerFactories()
    {
        if ( ! app()->environment('production') && $this->app->runningInConsole()) {
            app(Factory::class)->load(module_path('Coin', 'Database/factories'));
        }
    }

    /**
     * Register the service command.
     *
     * @return void
     */
    public function registerSchedules()
    {
        $this->addSchedules(function($schedule) {
            /** @var Schedule $schedule */

//            $schedule->job(new SyncCoinPriceJob)
//                     ->withoutOverlapping()
//                     ->everyMinute();

//            $schedule->job(new CheckTradeJob())
//                     ->withoutOverlapping()
//                     ->everyTwoMinutes();
//
//            $schedule->job(new SyncTradeStateJob())
//                ->withoutOverlapping()
//                ->everyFiveMinutes();

            /*//每小时检测系统钱包余额
            $schedule->job(new SystemWalletBalanceNotificationJob())
                ->withoutOverlapping()
                ->hourly();

            //每小时检测币种总余额数
            $schedule->job(new CoinBalanceTotalCheckNotificationJob())
                ->withoutOverlapping()
                ->hourly();*/

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
}
