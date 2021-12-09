<?php

namespace Modules\Coinv2\Components\Exchange;

use ccxt\Exchange;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class Manager
{
    /**
     * @var Container
     */
    protected $app;
    /**
     * @var ConfigRepository
     */
    protected $config;
    /**
     * @var array
     */
    protected $exchanges = [];

    public function __construct(Container $app)
    {
        $this->app = $app;
        $this->config = $app['config'];
    }

    /**
     * @return Exchange
     */
    public function priceSyncExchange()
    {
        return $this->exchange($this->config('price_sync_exchange'));
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function exchange($name)
    {
        if ( ! array_key_exists($name, $this->exchanges)) {
            $config = $this->config('exchanges.' . $name);
            if ( ! is_array($config)) {
                throw new \InvalidArgumentException(trans('错误的交易所配置数据'));
            }
            $class = $config['class'] ?? null;
            $this->exchanges[$name] = new $class($config['options'] ?? []);
        }

        return $this->exchanges[$name];
    }

    /**
     * Get a specific config data from a configuration file.
     *
     * @param string $key
     *
     * @param string|null $default
     *
     * @return mixed
     */
    public function config(string $key, $default = null)
    {
        return $this->config->get('coin::exchange.' . $key, $default);
    }
}
