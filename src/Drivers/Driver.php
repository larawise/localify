<?php

namespace Larawise\Localify\Drivers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Larawise\Localify\Contracts\LocalifyContract as LocalifyDriver;
use Larawise\Localify\Events\Language\CurrencyCreated;
use Larawise\Localify\Events\Language\CurrencyDeleted;
use Larawise\Localify\Events\Language\CurrencyUpdated;
use Larawise\Localify\Events\Language\LanguageCreated;
use Larawise\Localify\Events\Language\LanguageDeleted;
use Larawise\Localify\Events\Language\LanguageUpdated;
use Larawise\Localify\Events\Timezone\TimezoneCreated;
use Larawise\Localify\Events\Timezone\TimezoneDeleted;
use Larawise\Localify\Events\Timezone\TimezoneUpdated;
use Larawise\Localify\Exceptions\LocalifyException;
use Larawise\Support\Driver as LarawiseDriver;

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
abstract class Driver extends LarawiseDriver implements LocalifyDriver
{
    /**
     * The in-memory currencies storage.
     *
     * @var array
     */
    protected $currencies;

    /**
     * The in-memory languages storage.
     *
     * @var array
     */
    protected $languages = [];

    /**
     * The in-memory timezones storage.
     *
     * @var array
     */
    protected $timezones = [];

    /**
     * The list of guarded setting keys.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The loaded state of the contexts.
     *
     * @var bool
     */
    protected $loaded = false;

    /**
     * The unsaved state of the current operation.
     *
     * @var bool
     */
    protected $unsaved = false;

    /**
     * The formatter instance.
     *
     * @var FormatterContract
     */
    protected $formatter;

    /**
     * The normalizer instance.
     *
     * @var NormalizerContract
     */
    protected $normalizer;

    /**
     * Create a new localify driver instance.
     *
     * @param Repository $config
     * @param Encrypter $encrypter
     * @param Dispatcher $events
     *
     * @return void
     */
    public function __construct($config, $encrypter, $events, $formatter, $normalizer)
    {
        parent::__construct($config, $encrypter, $events);

        $this->formatter = $formatter;
        $this->normalizer = $normalizer;
    }

    /**
     * Compare in-memory localization data with stored data and extract detailed diffs.
     *
     * @param array $languages
     * @param array $currencies
     * @param array $timezones
     *
     * @return array
     */
    protected function audit($languages, $currencies, $timezones): array
    {
        $this->load();

        // Use current memory if no override provided
        $items = collect([
            'languages' => $languages,
            'currencies' => $currencies,
            'timezones' => $timezones,
        ]);

        // Initialize diff collections
        $added = collect();
        $updated = collect();
        $deleted = collect();

        // Loop through each context group
        $items->each(function ($current, string $group) use (&$added, &$updated, &$deleted) {
            $stored = collect($this->read($group));

            // Normalize current and stored to collections keyed by code
            $current = collect($current);
            $stored = collect($stored);

            // Detect added and updated
            $current->each(function ($currentData, string $code) use ($group, $stored, &$added, &$updated) {
                $dotKey = "{$group}.{$code}";

                if (!$stored->has($code)) {
                    // New item — add full data
                    $added->put($dotKey, $currentData);
                    return;
                }

                // Compare field-level changes
                $diff = collect($currentData)->map(function ($value, $field) use ($stored, $code) {
                    $old = $stored[$code][$field] ?? null;
                    return $old !== $value ? ['from' => $old, 'to' => $value] : null;
                })->filter();

                if ($diff->isNotEmpty()) {
                    $updated->put($dotKey, $diff->toArray());
                }
            });

            // Detect deleted
            $stored->each(function ($_value, string $code) use ($group, $current, &$deleted) {
                if (!$current->has($code)) {
                    $deleted->push("{$group}.{$code}");
                }
            });
        });

        return [
            'added'   => $added,
            'updated' => $updated,
            'deleted' => $deleted,
        ];
    }

    /**
     * Retrieve all localization items for the given context.
     *
     * @param string $source
     *
     * @return array<string, array>
     * @throws LocalifyException
     */
    public function all($source)
    {
        $this->load();

        return $this->source($source);
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
    abstract protected function delete($languages, $currencies, $timezones);

    /**
     * Remove one or more localization items from the specified context.
     *
     * @param string $source
     * @param string|array $code
     * @param bool $force
     *
     * @return LocalifyDriver
     */
    public function forget($source, $code, $force = false)
    {
        // Prevent deletion of guarded keys unless forced
        if (! $this->shouldBeProtected($code, $force)) {
            return $this;
        }

        // Ensure settings are loaded before accessing them
        $this->load();

        $items = $this->source($source);

        // Remove the specified key using Laravel's dot notation helper
        Arr::forget($items, $code);

        // Mark the settings as modified so they can be persisted later
        $this->unsaved = true;

        return $this;
    }

    /**
     * Clear all localization items from the specified context.
     *
     * @param string $source
     *
     * @return LocalifyDriver
     */
    public function flush($source)
    {
        // Ensure settings are loaded before flushing
        $this->load();

        // Replace the target collection with an empty one
        match ($source) {
            'languages' => $this->languages = [],
            'currencies' => $this->currencies = [],
            'timezones' => $this->timezones = []
        };

        // Mark the settings as modified (unsaved)
        $this->unsaved = true;

        return $this;
    }

    /**
     * Retrieve a localization value from the specified context target.
     *
     * @param string $source
     * @param string $code
     * @param string|array $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($source, $code, $key, $default = null)
    {
        // Ensure settings are loaded before accessing them
        $this->load();

        // If multiple keys are provided, delegate to getMany()
        if (is_array($key)) {
            return $this->getMany($source, $code, $key);
        }

        // Retrieve the value using Laravel's dot notation
        return Arr::get(
            array: $this->source($source),
            key: "{$code}.{$key}",
            default: $default
        );
    }

    /**
     * Retrieve multiple localization values from the specified context.
     *
     * @param string $source
     * @param string $code
     * @param array $keys
     *
     * @return array<string, mixed>
     */
    public function getMany($source, $code, $keys)
    {
        $payload = [];

        foreach ($keys as $value => $default) {
            // If the array is numerically indexed, treat value as key and default as null
            if (is_numeric($value)) {
                [$value, $default] = [$default, null];
            }

            // Retrieve the value using the get() method
            $payload[$value] = $this->get($source, $code, $value, $default);
        }

        return $payload;
    }

    /**
     * Check if one or more localization codes exist in the specified context.
     *
     * @param string $source
     * @param string|array $code
     *
     * @return bool|array<string, bool>
     */
    public function has($source, $code)
    {
        // Ensure settings are loaded before accessing them
        $this->load();

        // If multiple keys are provided, delegate to hasMany()
        if (is_array($code)) {
            return $this->hasMany($source, $code);
        }

        // Check if the key exists in the loaded settings array
        return Arr::has($this->source($source), $code);
    }

    /**
     * Check existence of multiple localization codes in the specified context.
     *
     * @param string $source
     * @param string[] $codes
     *
     * @return array<string, bool>
     */
    public function hasMany($source, $codes)
    {
        $payload = [];

        foreach ($codes as $code) {
            // Check if each key exists in the current settings
            $payload[$code] = $this->has($source, $code);
        }

        return $payload;
    }

    /**
     * Load localization data from the underlying storage into memory.
     *
     * @param bool $force
     *
     * @return void
     */
    protected function load($force = false)
    {
        // Load settings only if not already loaded or if forced
        if (! $this->loaded || $force) {
            // Read from storage (e.g. file or database)
            $this->currencies = $this->read('currencies');
            $this->languages = $this->read('languages');
            $this->timezones = $this->read('timezones');

            // Mark as loaded to prevent redundant reads
            $this->loaded = true;
        }
    }

    /**
     * Persist all modified localization data to storage.
     *
     * @return bool
     */
    public function save()
    {
        // Skip saving if no changes were made
        if (! $this->unsaved) {
            return false;
        }

        // Synchronize settings by writing current items and deleting missing ones.
        $this->sync(
            $this->languages,
            $this->currencies,
            $this->timezones
        );

        // Clear the unsaved flag to indicate a clean state
        $this->unsaved = false;

        // Indicate that save was successfully performed
        return true;
    }

    /**
     * Set one or more localization values for a specific item.
     *
     * @param string $target
     * @param string $code
     * @param string|array $key
     * @param mixed $value
     * @param bool $force
     *
     * @return LocalifyDriver
     */
    public function set($target, $code, $key, $value = null, $force = false)
    {
        // Skip guarded keys unless forced
        if ($this->shouldBeProtected($key, $force)) {
            return $this;
        }

        // Ensure settings are loaded before modifying them
        $this->load();

        // If multiple keys are provided, delegate to setMany()
        if (is_array($key)) {
            return $this->setMany($target, $code, $key, $force);
        }

        // Store the value using Laravel's dot notation
        Arr::set($this->$target, "{$code}.{$key}", $value);

        // Mark the settings as modified for later persistence
        $this->unsaved = true;

        return $this;
    }

    /**
     * Set multiple localization values for a specific item.
     *
     * @param string $target
     * @param string $code
     * @param array $keys
     * @param bool $force
     *
     * @return LocalifyDriver
     */
    public function setMany($target, $code, $keys, $force = false)
    {
        foreach ($keys as $key => $value) {
            $this->set($target, $code, $key, $value, $force);
        }

        return $this;
    }

    /**
     * Determine whether a key is guarded and should be skipped during mutation.
     *
     * @param string $key
     * @param bool $force
     *
     * @return bool
     */
    protected function shouldBeProtected($key, $force = false)
    {
        // Guarding explicitly bypassed
        if ($force) {
            return false;
        }

        foreach ($this->guarded as $pattern) {
            // Check if the identifier matches the current pattern (supports wildcards)
            if (Str::is($pattern, $key)) {
                // Match found — encryption should be applied
                return true;
            }
        }

        // No match — skip encryption for this identifier
        return false;
    }

    /**
     * Synchronize localization data with storage.
     *
     * @param array $languages
     * @param array $currencies
     * @param array $timezones
     *
     * @return void
     */
    protected function sync($languages, $currencies, $timezones)
    {
        // Write current settings to storage
        $this->write($languages, $currencies, $timezones);

        // Delete settings that are no longer present
        $this->delete($languages, $currencies, $timezones);
    }

    /**
     * Read localization data from storage for the given context.
     *
     * @param string $source
     *
     * @return array<string, array>
     */
    abstract protected function read($source);

    /**
     * Dynamically dispatch group-specific event based on action type.
     *
     * @param string $action
     * @param string $group
     * @param string $code
     * @param array $payload
     *
     * @return void
     */
    protected function fireEvent(string $action, string $group, string $code, array $payload): void
    {
        $class = match ("{$group}.{$action}") {
            'languages.created'  => LanguageCreated::class,
            'languages.updated'  => LanguageUpdated::class,
            'languages.deleted'  => LanguageDeleted::class,

            'currencies.created' => CurrencyCreated::class,
            'currencies.updated' => CurrencyUpdated::class,
            'currencies.deleted' => CurrencyDeleted::class,

            'timezones.created'  => TimezoneCreated::class,
            'timezones.updated'  => TimezoneUpdated::class,
            'timezones.deleted'  => TimezoneDeleted::class,

            default => throw new LocalifyException("Unknown event for {$group} {$action}"),
        };

        $this->dispatch(new $class($code, $payload));
    }

    /**
     * Retrieve the in-memory dataset for the given context.
     *
     * @param string $target
     *
     * @return array<string, array>
     * @throws LocalifyException
     */
    protected function source($target)
    {
        if (! in_array($target, ['languages', 'currencies', 'timezones'], true)) {
            throw new LocalifyException("Invalid target : {$target}");
        }

        return match ($target) {
            'languages' => $this->languages,
            'currencies' => $this->currencies,
            'timezones' => $this->timezones
        };
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
    abstract protected function write($languages, $currencies, $timezones);
}
