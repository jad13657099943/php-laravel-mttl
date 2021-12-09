<?php

namespace Modules\Coinv2\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Coinv2\Events\TokenioNoticeReceived;
use Modules\Coinv2\Listeners\TokenioNoticeReceivedListener;

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
