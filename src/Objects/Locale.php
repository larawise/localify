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
final class Locale implements Stringable
{
    /**
     * ISO 639-1 language code (e.g. "tr", "en").
     *
     * @var string|null
     */
    public $language;

    /**
     * ISO 3166-1 country code (e.g. "TR", "US").
     *
     * @var string|null
     */
    public $country;

    /**
     * Original raw locale string before normalization.
     *
     * @var mixed
     */
    public $original;

    /**
     * Internal cache for parsed locale strings.
     *
     * @var array<string, self>
     */
    private static $cache = [];

    /**
     * Regex pattern used to parse locale strings.
     *
     * @var string
     */
    private static $regex = '/^(?:(?<lang>[a-z]{2})[-]?(?<country>[A-Z]{2})?|(?<country_alt>[A-Z]{2})[-]?(?<lang_alt>[a-z]{2}))$/i';

    /**
     * Create a new locale instance.
     *
     * @param string|null $language
     * @param string|null $country
     * @param mixed $original
     *
     * @return void
     */
    public function __construct(?string $language, ?string $country, ?string $original = null)
    {
        $this->language = $language ? strtolower($language) : null;
        $this->country  = $country ? strtoupper($country) : null;
        $this->original = $original ?? '';
    }

    /**
     * Create a Locale object from a raw string using regex parsing.
     *
     * @param string|null $value
     * @param string|null $regex
     *
     * @return static
     */
    public static function from($value, $regex = null)
    {
        $normalized = trim(str_replace(['_', ' '], '-', (string) $value));
        $key = md5(($regex ?? '') . '|' . $normalized);

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (blank($normalized)) {
            return self::$cache[$key] = new static(null, null, $value);
        }

        $pattern = $regex ?? static::$regex;

        preg_match($pattern, $normalized, $matches);

        $language = $matches['lang'] ?? $matches['lang_alt'] ?? null;
        $country = $matches['country'] ?? $matches['country_alt'] ?? null;

        return self::$cache[$key] = new static($language, $country, $value);
    }

    /**
     * Convert the locale to string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->language && $this->country) {
            return "{$this->language}-{$this->country}";
        }

        if ($this->language) {
            return $this->language;
        }

        if ($this->country) {
            return $this->country;
        }

        return '';
    }

    /**
     * Check if both language and country are null.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->language === null && $this->country === null;
    }

    /**
     * Check if original string matches normalized output.
     *
     * @return bool
     */
    public function isNormalized()
    {
        return strtolower(trim(str_replace(['_', ' '], '-', $this->original))) === (string) $this;
    }
}
