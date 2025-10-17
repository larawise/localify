<?php

namespace Larawise\Localify\Drivers;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Query\Builder;
use Larawise\Localify\Exceptions\LocalifyException;
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
class DatabaseDriver extends Driver
{
    use Connectable;

    /**
     * The name of the languages table.
     *
     * @var string
     */
    protected $languageTable;

    /**
     * The name of the currencies table.
     *
     * @var string
     */
    protected $currencyTable;

    /**
     * The name of the timezones table.
     *
     * @var string
     */
    protected $timezoneTable;

    /**
     * The relation of the localizaton tables.
     *
     * @var array
     */
    protected $relations = [
        'languages' => 'languageTable',
        'currencies' => 'currencyTable',
        'timezones' => 'timezoneTable',
    ];

    /**
     * Create a new database localization driver instance.
     *
     * @param $config
     * @param ConnectionResolverInterface $db
     * @param Encrypter $encrypter
     * @param Dispatcher $events
     * @param $formatter
     * @param $normalizer
     *
     * @return void
     */
    public function __construct($config, $db, $encrypter, $events, $formatter, $normalizer)
    {
        parent::__construct($config, $encrypter, $events, $formatter, $normalizer);

        $this->setConnectionResolver($db);
        $this->setConnectionName($this->config('localify.drivers.database.connection', 'mysql'));

        $this->languageTable = $this->config('localify.drivers.database.tables.language', 'localify_languages');
        $this->currencyTable = $this->config('localify.drivers.database.tables.currency', 'localify_currencies');
        $this->timezoneTable = $this->config('localify.drivers.database.tables.timezone', 'localify_timezones');
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
        // Generate diff between current memory and stored data
        $changes = $this->audit($languages, $currencies, $timezones);

        // Handle deleted items
        $changes['deleted']->each(function (string $key) {
            // Extract group and code from dot-key (e.g. 'languages.tr')
            [$group, $code] = explode('.', $key, 2);

            // Delete item from corresponding table
            $this->query($group)
                ->where('code', $code)
                ->delete();

            // Dispatch group-specific deletion event
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
        // Generate diff between current memory and stored data
        $changes = $this->audit($languages, $currencies, $timezones);

        // Handle newly added items
        $changes['added']->each(function (array $data, string $key) {
            // Extract group and code from dot-key (e.g. 'languages.tr')
            [$group, $code] = explode('.', $key, 2);

            // Prepare insert payload with timestamps
            $payload = array_merge(
                ['code' => $code],
                $data,
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Insert new item into corresponding table
            $this->query($group)->insert($payload);

            // Dispatch group-specific creation event
            $this->fireEvent('added', $group, $code, $payload);
        });

        // Handle updated items (only changed fields)
        $changes['updated']->each(function (array $fields, string $key) {
            // Extract group and code from dot-key
            [$group, $code] = explode('.', $key, 2);

            // Extract only new values from field-level diff
            $update = collect($fields)->map(fn($change) => $change['to'])->all();

            // Prepare update payload with timestamp
            $payload = array_merge($update, ['updated_at' => now()]);

            // Update existing item with changed fields
            $this->query($group)
                ->where('code', $code)
                ->update($payload);

            // Dispatch group-specific update event
            $this->fireEvent('updated', $group, $code, $payload);
        });
    }

    /**
     * Read data from the underlying storage.
     *
     * @param string $source
     *
     * @return array
     */
    protected function read($source)
    {
        // Only check connection once
        if ($this->connected === null) {
            $this->connected = $this->isConnected();
        }

        // Abort if connection is invalid
        if (! $this->connected) {
            return [];
        }

        // Fetch and parse target data from the database
        return $this->query($source)->get()->keyBy('code')->toArray();
    }

    /**
     * Get a fresh query builder instance for the given context table.
     *
     * @param string $target
     *
     * @return Builder
     * @throws LocalifyException
     */
    protected function query($target = 'languages')
    {
        if (! isset($this->relations[$target])) {
            throw new LocalifyException("Unknown target: {$target}");
        }

        $property = $this->relations[$target];

        return $this->db()->table($this->$property)->useWritePdo();
    }
}
