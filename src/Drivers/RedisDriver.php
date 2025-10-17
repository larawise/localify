<?php

namespace Larawise\Localify\Drivers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Redis\Factory;
use Larawise\Support\Traits\Connectable;

/**
 * Srylius - The ultimate symphony for technology architecture!
 *
 * @package     Larawise
 * @subpackage  Localify
 * @version     v1.0.0
 * @author      Selçuk Çukur <hk@selcukcukur.com.tr>
 * @copyright   Srylius Teknoloji Limited Şirketi
 *
 * @see https://docs.larawise.com/ Larawise : Docs
 */
class RedisDriver extends Driver
{
    use Connectable;

    /**
     * Create a new database setting driver instance.
     *
     * @param Repository $config
     * @param Encrypter $encrypter
     * @param Dispatcher $events
     * @param Factory $redis
     * @param $formatter
     * @param $normalizer
     *
     * @return void
     */
    public function __construct($config, $encrypter, $events, $redis, $formatter, $normalizer)
    {
        parent::__construct($config, $encrypter, $events, $formatter, $normalizer);

        $this->setRedisFactory($redis);
        $this->setConnectionName($this->config('localify.drivers.redis.connection', 'default'));
    }

    /**
     * Permanently delete localization data for the given context target.
     *
     * @param array $languages
     * @param array $currencies
     * @param array $timezones
     *
     * @return void
     */
    protected function delete($languages, $currencies, $timezones)
    {
        $changes = $this->audit($languages, $currencies, $timezones);

        $changes['deleted']->each(function (string $key) {
            [$group, $code] = explode('.', $key, 2);

            $this->redis()->del("localify:{$group}:{$code}");

            $this->fireEvent('deleted', $group, $code, []);
        });
    }

    /**
     * Write localization data to the underlying storage layer.
     *
     * @param array $languages
     * @param array $currencies
     * @param array $timezones
     *
     * @return void
     */
    protected function write($languages, $currencies, $timezones)
    {
        $changes = $this->audit($languages, $currencies, $timezones);

        $changes['added']->each(function (array $data, string $key) {
            [$group, $code] = explode('.', $key, 2);

            $payload = array_merge(['code' => $code], $data, [
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ]);

            $this->redis()->set("localify:{$group}:{$code}", $payload);

            $this->fireEvent('added', $group, $code, $payload);
        });

        $changes['updated']->each(function (array $fields, string $key) {
            [$group, $code] = explode('.', $key, 2);

            $update = collect($fields)->map(fn($change) => $change['to'])->all();

            $payload = array_merge($update, ['updated_at' => now()->toISOString()]);

            $this->redis()->set("localify:{$group}:{$code}", $payload);

            $this->fireEvent('updated', $group, $code, $payload);
        });
    }

    /**
     * Read settings from the underlying storage.
     *
     * @param $source
     *
     * @return array
     */
    protected function read($source)
    {
        // Check connection only once per lifecycle
        if ($this->connected === null) {
            $this->connected = $this->isConnected();
        }

        // Abort if Redis is unreachable
        if (! $this->connected) {
            return [];
        }

        // Fetch all keys matching the Settingfy prefix
        $keys = $this->redis()->keys("localify:{$source}:*");
        $items = [];

        foreach ($keys as $fullKey) {
            // Extract code from key (e.g. 'localify:languages.tr' → 'tr')
            $code = str_replace("{$this->getRedisPrefix()}localify:{$source}.", '', $fullKey);

            // Fetch and decode value
            $raw = $this->redis()->get($fullKey);
            $items[$code] = unserialize($raw);
        }

        return $items;
    }
}
