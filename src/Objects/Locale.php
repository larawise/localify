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
    private $language;

    /**
     * ISO 3166-1 country code (e.g. "TR", "US").
     *
     * @var string|null
     */
    private $country;

    /**
     * Original raw locale string before normalization.
     *
     * @var mixed
     */
    private $original;

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
     * Create a new Locale instance.
     *
     * @param string|null $language
     * @param string|null $country
     * @param string|null $original
     *
     * @return void
     */
    public function __construct(?string $language, ?string $country, ?string $original = '')
    {
        $this->language = $language ? strtolower($language) : null;
        $this->country  = $country ? strtoupper($country) : null;
        $this->original = $original ?? '';
    }

    /**
     * Get the country code.
     *
     * @return string|null
     */
    public function country()
    {
        return $this->country;
    }

    /**
     * Check if the locale is semantically empty.
     *
     * @return bool
     */
    public function empty()
    {
        return $this->language === null && $this->country === null;
    }

    /**
     * Parse a raw locale string into a Locale object.
     *
     * @param string|null $value
     *
     * @return static
     */
    public static function from($value)
    {
        $normalized = self::normalize($value);
        $key = md5($normalized);

        return self::$cache[$key] ??= self::parse($normalized, $value, self::$regex);
    }

    /**
     * Get the language code.
     *
     * @return string|null
     */
    public function language()
    {
        return $this->language;
    }

    /**
     * Normalize a raw locale string.
     *
     * @param string|null $value
     *
     * @return string
     */
    private static function normalize($value)
    {
        return strtolower(trim(str_replace(['_', ' '], '-', (string) $value)));
    }

    /**
     * Check if original input matches normalized output.
     *
     * @return bool
     */
    public function normalized()
    {
        return self::normalize($this->original) === (string) $this;
    }

    /**
     * Parse normalized string using regex.
     *
     * @param string $normalized
     * @param string|null $original
     * @param string $pattern
     *
     * @return static
     */
    private static function parse($normalized, $original, $pattern)
    {
        if (blank($normalized)) {
            return new self(null, null, $original ?? '');
        }

        preg_match($pattern, $normalized, $matches);

        $language = $matches['lang'] ?? $matches['lang_alt'] ?? null;
        $country  = $matches['country'] ?? $matches['country_alt'] ?? null;

        return new self($language, $country, $original ?? '');
    }

    /**
     * Get the original raw input.
     *
     * @return string
     */
    public function original()
    {
        return $this->original;
    }

    /**
     * Convert the locale to array representation.
     *
     * @return array{
     *     original: string,
     *     language: string|null,
     *     country: string|null,
     *     value: string,
     *     empty: bool,
     *     normalized: bool,
     *     valid: bool
     * }
     */
    public function toArray()
    {
        return [
            'original'   => $this->original(),
            'language'   => $this->language(),
            'country'    => $this->country(),
            'value'      => (string) $this,
            'empty'      => $this->empty(),
            'normalized' => $this->normalized(),
            'valid'      => $this->valid(),
        ];
    }

    /**
     * Check if the locale is valid (has at least one component).
     *
     * @return bool
     */
    public function valid(): bool
    {
        return in_array($this->language, LocaleCodes::LANGUAGES, true)
            || in_array($this->country, LocaleCodes::COUNTRIES, true);
    }

    /**
     * Convert the locale to string (e.g. "tr-TR", "en", "US").
     *
     * @return string
     */
    public function __toString()
    {
        return match (true) {
            $this->language && $this->country => "{$this->language}-{$this->country}",
            $this->language => $this->language,
            $this->country => $this->country,
            default => '',
        };
    }
}
