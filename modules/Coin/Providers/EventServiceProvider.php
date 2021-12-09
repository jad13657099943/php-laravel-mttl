<?php

namespace Modules\Coin\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Coin\Events\TokenioNoticeReceived;
use Modules\Coin\Listeners\TokenioNoticeReceivedListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TokenioNoticeReceived::class  => [
            TokenioNoticeReceivedListener::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];
}
