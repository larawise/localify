<?php

namespace Larawise\Localify;

use Illuminate\Contracts\Container\BindingResolutionException;
use Larawise\Localify\Drivers\DatabaseDriver;
use Larawise\Localify\Drivers\RedisDriver;
use Larawise\Support\Manager;

/**
 * Srylius - The ultimate symphony for technology architecture!
 *
 * @package     Larawise
 * @subpackage  Convertify
 * @version     v1.0.0
 * @author      Selçuk Çukur <hk@selcukcukur.com.tr>
 * @copyright   Srylius Teknoloji Limited Şirketi
 *
 * @see https://docs.larawise.com/ Larawise : Docs
 */
class LocalifyManager extends Manager
{
    /**
     * Create an instance of the database setting driver.
     *
     * @return DatabaseDriver
     * @throws BindingResolutionException
     */
    protected function createDatabaseDriver()
    {
        // Instantiate and return the localify service with a database store backend
        return new DatabaseDriver(
            config: $this->config,
            db: $this->container->make('db'),
            encrypter: $this->container->make('encrypter'),
            events: $this->container->make('events'),
            formatter: new \stdClass,
            normalizer: new \stdClass()
        );
    }

    /**
     * Create an instance of the redis setting driver.
     *
     * @return RedisDriver
     * @throws BindingResolutionException
     */
    protected function createRedisDriver()
    {
        // Instantiate and return the settingfy service with a database store backend
        return new RedisDriver(
            config: $this->config,
            encrypter: $this->container->make('encrypter'),
            events: $this->container->make('events'),
            redis: $this->container->make('redis'),
            formatter: new \stdClass,
            normalizer: new \stdClass()
        );
    }

    /**
     * Get the convertify converter configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config["localify.drivers.{$name}"] ?: [];
    }

    /**
     * Get the default converter name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config['localify.storage'];
    }

    /**
     * Set the default localify driver name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setDefaultDriver($name)
    {
        return $this->config['localify.storage'];
    }
}
