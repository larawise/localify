<?php

namespace Larawise\Localify\Objects;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Exception;
use Larawise\Localify\Contracts\TimezoneContract;

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
final class Timezone implements TimezoneContract
{
    /**
     * Original raw timezone string before normalization.
     *
     * @var mixed
     */
    private $input;

    /**
     * Final normalized timezone string (e.g. "Europe/Istanbul", "Etc/GMT-3").
     *
     * @var string|null
     */
    private $timezone;

    /**
     * Timezone region (e.g. "Europe", "America").
     *
     * @var string|null
     */
    private $region;

    /**
     * Timezone city or identifier (e.g. "Istanbul", "New_York").
     *
     * @var string|null
     */
    private $zone;

    /**
     * Indicates whether the input string was normalized during instantiation.
     *
     * @var bool
     */
    private $normalized;

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
     * Lazily cached DateTimeZone instance for the current timezone.
     *
     * @var DateTimeZone|null
     */
    private $resolvedZone = null;

    /**
     * Lazily cached DateTime instance set to the current timezone.
     *
     * @var Carbon|null
     */
    private $resolvedTime = null;

    /**
     * Create a new timezone instance.
     *
     * @param string|null $region
     * @param string|null $zone
     * @param mixed $input
     * @param bool $normalized
     *
     * @return void
     */
    public function __construct($region, $zone, $input = null, $normalized = false)
    {
        $this->region = $region;
        $this->zone = $zone;
        $this->input = $input;
        $this->timezone = $region && $zone
            ? ucfirst($region) . '/' . str_replace(' ', '_', $zone)
            : Timezone::normalize($input);
        $this->normalized = $normalized;
    }

    /**
     * Get the timezone abbreviation (e.g. "EEST", "EST", "JST").
     *
     * @return string|null
     */
    public function abbr()
    {
        return $this->resolve() ? $this->resolvedTime->format('T') : null;
    }

    /**
     * Determine if daylight saving time (DST) is currently active.
     *
     * @return bool
     */
    public function dst()
    {
        return $this->resolve() && $this->resolvedTime->format('I') === '1';
    }

    /**
     * Check if the timezone is semantically empty.
     *
     * @return bool
     */
    public function empty()
    {
        return empty($this->timezone);
    }

    /**
     * Create a Timezone object from a raw string using regex parsing.
     *
     * @param string|null $value
     *
     * @return static
     */
    public static function from($value)
    {
        $key = trim((string) $value);

        if (isset(Timezone::$cache[$key])) {
            return Timezone::$cache[$key];
        }

        $normalized = Timezone::normalize($key);
        $wasNormalized = $normalized === $key;

        $region = null;
        $zone = null;

        if (preg_match(self::$regex, $normalized, $match)) {
            $region = $match['region'];
            $zone = $match['zone'];
        }

        $instance = new Timezone($region, $zone, $key);
        $instance->timezone = $region && $zone
            ? ucfirst($region) . '/' . str_replace(' ', '_', $zone)
            : $normalized;
        $instance->normalized = ! $wasNormalized;

        return self::$cache[$key] = $instance;
    }

    /**
     * Convert the timezone object into a JSON-serializable array.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Check if original string matches normalized output.
     *
     * @return bool
     */
    public function normalized()
    {
        return $this->normalized;
    }

    /**
     * Normalize raw timezone input into standard format.
     *
     * @param string|null $value
     *
     * @return string
     */
    private static function normalize($value)
    {
        $value = trim($value);

        // POSIX GMT formatı: "UTC+3", "GMT -2", "Etc GMT+2"
        if (preg_match('/^(Etc\s+)?(UTC|GMT)\s*([+-]\d{1,2})$/i', $value, $match)) {
            $prefix = 'Etc/';
            $sign   = $match[3][0];
            $number = substr($match[3], 1);
            $inverted = $sign === '+' ? '-' : '+';
            return $prefix . 'GMT' . $inverted . $number;
        }

        // Region City formatı: "Europe Istanbul"
        if (str_contains($value, ' ') && ! str_contains($value, '/')) {
            $parts = explode(' ', $value, 2);
            if (count($parts) === 2) {
                return ucfirst($parts[0]) . '/' . str_replace(' ', '_', $parts[1]);
            }
        }

        return $value;
    }

    /**
     * Get the UTC offset as a formatted string (e.g. "UTC+3", "UTC-5").
     *
     * @return string|null
     */
    public function offset()
    {
        $minutes = $this->offsetMinutes();

        if ($minutes === null)
            return null;

        $sign = $minutes >= 0 ? '+' : '-';
        $abs = abs($minutes);
        $hours = floor($abs / 60);
        $remMin = $abs % 60;

        return $remMin === 0
            ? "UTC{$sign}{$hours}"
            : sprintf("UTC%s%d:%02d", $sign, $hours, $remMin);
    }

    /**
     * Get the UTC offset in minutes for this timezone.
     *
     * @return int|null
     */
    public function offsetMinutes()
    {
        return $this->resolve() ? $this->resolvedTime->getOffset() / 60 : null;
    }

    /**
     * Get the UTC offset in seconds for this timezone.
     *
     * @return int|null
     */
    public function offsetSeconds()
    {
        if (! $this->resolve()) {
            return null;
        }

        return $this->resolvedTime->getOffset();
    }

    /**
     * Get the original raw input before normalization.
     *
     * @return mixed
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * Get the region part of the timezone (e.g. "Europe", "America").
     *
     * @return string|null
     */
    public function region()
    {
        return $this->region;
    }

    /**
     * Get the fully normalized timezone string (e.g. "Europe/Istanbul", "Etc/GMT-3").
     *
     * @return string|null
     */
    public function timezone()
    {
        return $this->timezone;
    }

    /**
     * Get the resolved current time in the timezone as a formatted string.
     *
     * @return string|null
     */
    public function time()
    {
        return $this->resolve() ? $this->resolvedTime?->format('Y-m-d H:i:s') : null;
    }

    /**
     * Get geographic metadata for the resolved timezone, if available.
     *
     * @return array{
     *     country_code: string,
     *     latitude: float,
     *     longitude: float,
     *     comments: string
     * }|null
     */
    public function type()
    {
        return $this->resolve() ? $this->resolvedZone?->getLocation() ?: null : null;
    }

    /**
     * Convert the timezone object into an array representation.
     *
     * @return array{
     *     input: mixed,
     *     timezone: string|null,
     *     region: string|null,
     *     zone: string|null,
     *     valid: bool,
     *     empty: bool,
     *     normalized: bool,
     *     transitionless: bool,
     *     offset: string|null,
     *     offsetMin: int|null,
     *     offsetSec: int|null,
     *     abbr: string|null,
     *     dst: bool,
     *     carbon: Carbon|null
     * }
     */
    public function toArray()
    {
        return [
            'input'         => $this->input(),
            'timezone'      => $this->timezone(),
            'region'        => $this->region(),
            'zone'          => $this->zone(),
            'valid'         => $this->valid(),
            'empty'         => $this->empty(),
            'normalized'    => $this->normalized(),
            'transitionless'=> $this->transitionless(),
            'offset'        => $this->offset(),
            'offsetMin'     => $this->offsetMinutes(),
            'offsetSec'     => $this->offsetSeconds(),
            'abbr'          => $this->abbr(),
            'dst'           => $this->dst(),
            'carbon'        => $this->toCarbon(),
            'resolved'      => [
                'type' => $this->type(),
                'zone' => $this->resolvedZone?->getName(),
                'time' => $this->time(),
            ]
        ];
    }

    /**
     * Convert the timezone into a Carbon instance for the current time.
     *
     * @return Carbon|null
     */
    public function toCarbon()
    {
        return $this->resolve() ? $this->resolvedTime : null;
    }

    /**
     * Get a single-line CLI-friendly summary.
     *
     * @return string
     */
    public function toLine()
    {
        return sprintf(
            '%s | %s | %s | DST %s | %s | %s',
            $this->timezone() ?? '—',
            $this->offset() ?? '—',
            $this->abbr() ?? '—',
            $this->dst() ? '✅' : '❌',
            $this->transitionless() ? '🟢 Static' : '🔄 Dynamic',
            $this->valid() ? '✅' : '❌'
        );
    }

    /**
     * Check if the timezone has no transitions (e.g. DST changes).
     *
     * @return bool
     */
    public function transitionless()
    {
        if (! $this->resolve()) {
            return false;
        }

        foreach ($this->resolvedZone->getTransitions() as $transition) {
            if ($transition['isdst'] === true) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the timezone is valid and recognized by PHP.
     *
     * @return bool
     */
    public function valid()
    {
        try {
            new DateTimeZone($this->timezone);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Lazily resolve and cache DateTimeZone and DateTime instances for the current timezone.
     *
     * @return bool
     */
    private function resolve()
    {
        if (! $this->valid())
            return false;

        if (! $this->resolvedZone) {
            $this->resolvedZone = new DateTimeZone($this->timezone);
        }

        if (! $this->resolvedTime) {
            $this->resolvedTime = Carbon::now($this->resolvedZone);
        }

        return true;
    }

    /**
     * Get resolved timezone metadata including zone name, current time, and geographic type info.
     *
     * @return array{
     *     zone: string|null,
     *     time: string|null,
     *     type: array{
     *         country_code: string,
     *         latitude: float,
     *         longitude: float,
     *         comments: string
     *     }|null
     * }
     */
    public function resolved()
    {
        return [
            'zone' => $this->resolve() ? $this->resolvedZone?->getName() : null,
            'time' => $this->time(),
            'type' => $this->type(),
        ];
    }

    /**
     * Get the zone or city part of the timezone (e.g. "Istanbul", "New_York").
     *
     * @return string|null
     */
    public function zone()
    {
        return $this->zone;
    }

    /**
     * Convert the timezone object into an array representation.
     *
     * @return array
     */
    public function __toArray()
    {
        return $this->toArray();
    }

    /**
     * Customize the debug output for var_dump(), dd(), and similar tools.
     *
     * @return array{payload: array}
     */
    public function __debugInfo()
    {
        return [
            'payload' => $this->toArray(),
        ];
    }

    /**
     * Convert the timezone to string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->timezone;
    }
}
