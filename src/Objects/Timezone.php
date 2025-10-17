<?php

namespace Larawise\Localify\Objects;

use Stringable;

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
final class Timezone implements Stringable
{
    /**
     * Timezone region (e.g. "Europe", "America").
     *
     * @var string|null
     */
    public $region;

    /**
     * Timezone city or identifier (e.g. "Istanbul", "New_York").
     *
     * @var string|null
     */
    public $zone;

    /**
     * Original raw timezone string before normalization.
     *
     * @var mixed
     */
    public $original;

    /**
     * Internal cache for parsed timezone strings.
     *
     * @var array<string, self>
     */
    private static $cache = [];

    /**
     * Regex pattern used to parse timezone strings.
     *
     * @var string
     */
    private static $regex = '/^(?<region>[A-Za-z]+)\/(?<zone>[A-Za-z_\-]+)$/';

    /**
     * Create a new timezone instance.
     *
     * @param string|null $region
     * @param string|null $zone
     * @param mixed $original
     * 
     * @return void
     */
    public function __construct($region, $zone, $original = null)
    {
        $this->region   = $region ? strtolower($region) : null;
        $this->zone     = $zone ? str_replace(' ', '_', $zone) : null;
        $this->original = $original ?? '';
    }

    /**
     * Create a Timezone object from a raw string using regex parsing.
     *
     * @param string|null $value
     * @param string|null $regex
     *
     * @return static
     */
    public static function from($value, $regex = null)
    {
        $normalized = trim(str_replace(' ', '_', (string) $value));
        $key = md5(($regex ?? '') . '|' . $normalized);

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (blank($normalized)) {
            return self::$cache[$key] = new static(null, null, $value);
        }

        $pattern = $regex ?? static::$regex;

        preg_match($pattern, $normalized, $matches);

        $region = $matches['region'] ?? null;
        $zone   = $matches['zone'] ?? null;

        return self::$cache[$key] = new static($region, $zone, $value);
    }

    /**
     * Convert the timezone to string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->region && $this->zone) {
            return "{$this->region}/{$this->zone}";
        }

        return $this->region ?? $this->zone ?? '';
    }

    /**
     * Check if both region and zone are null.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->region === null && $this->zone === null;
    }

    /**
     * Check if original string matches normalized output.
     *
     * @return bool
     */
    public function isNormalized()
    {
        return strtolower(trim(str_replace(' ', '_', $this->original))) === (string) $this;
    }
}
