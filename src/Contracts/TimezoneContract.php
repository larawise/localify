<?php

namespace Larawise\Localify\Contracts;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
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
interface TimezoneContract extends Arrayable, Stringable
{
    /**
     * Get the timezone abbreviation (e.g. "EEST", "EST", "JST").
     *
     * @return string|null
     */
    public function abbr();

    /**
     * Determine if daylight saving time (DST) is currently active.
     *
     * @return bool
     */
    public function dst();

    /**
     * Check if the timezone is semantically empty.
     *
     * @return bool
     */
    public function empty();

    /**
     * Convert the timezone object into a JSON-serializable array.
     *
     * @return array
     */
    public function jsonSerialize();

    /**
     * Check if original string matches normalized output.
     *
     * @return bool
     */
    public function normalized();

    /**
     * Get the UTC offset as a formatted string (e.g. "UTC+3", "UTC-5").
     *
     * @return string|null
     */
    public function offset();

    /**
     * Get the UTC offset in minutes for this timezone.
     *
     * @return int|null
     */
    public function offsetMinutes();

    /**
     * Get the UTC offset in seconds for this timezone.
     *
     * @return int|null
     */
    public function offsetSeconds();

    /**
     * Get the original raw input before normalization.
     *
     * @return mixed
     */
    public function input();

    /**
     * Get the region part of the timezone (e.g. "Europe", "America").
     *
     * @return string|null
     */
    public function region();

    /**
     * Get the resolved current time in the timezone as a formatted string.
     *
     * @return string|null
     */
    public function time();

    /**
     * Get the fully normalized timezone string (e.g. "Europe/Istanbul", "Etc/GMT-3").
     *
     * @return string|null
     */
    public function timezone();

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
    public function type();

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
    public function toArray();

    /**
     * Convert the timezone into a Carbon instance for the current time.
     *
     * @return Carbon|null
     */
    public function toCarbon();

    /**
     * Get a single-line CLI-friendly summary.
     *
     * @return string
     */
    public function toLine();

    /**
     * Check if the timezone has no transitions (e.g. DST changes).
     *
     * @return bool
     */
    public function transitionless();

    /**
     * Check if the timezone is valid and recognized by PHP.
     *
     * @return bool
     */
    public function valid();

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
    public function resolved();

    /**
     * Get the zone or city part of the timezone (e.g. "Istanbul", "New_York").
     *
     * @return string|null
     */
    public function zone();
}
