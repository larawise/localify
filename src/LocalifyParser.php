<?php

namespace Larawise\Localify;

use Brick\Money\Exception\UnknownCurrencyException;
use Larawise\Localify\Exceptions\LocalifyException;
use Larawise\Localify\Objects\Currency;
use Larawise\Localify\Objects\Locale;

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
class LocalifyParser
{
    /**
     * Parse a localization value based on its type.
     *
     * @param mixed $value
     * @param string $type
     * @param mixed $context
     *
     * @return mixed
     * @throws UnknownCurrencyException
     */
    public static function parse(mixed $value, string $type, mixed $context = null)
    {
        return match ($type) {
            'currency' => static::parseCurrency($value),
            'language' => static::parseLanguage($value),
            'timezone' => static::parseTimezone($value),
            default => throw new LocalifyException("Unknown parse type: {$type}"),
        };
    }

    /**
     * Parses a currency expression and returns a Currency object.
     *
     * @param string $value
     *
     * @return Currency
     */
    public static function parseCurrencyString($value)
    {
        return Currency::from($value);
    }

    /**
     * Extracts the currency code from a currency expression.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function parseCurrency($value)
    {
        return static::parseCurrencyString($value)->currency;
    }

    /**
     * Determines the direction of the currency placement.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function parseCurrencyDirection($value)
    {
        return static::parseCurrencyString($value)->direction;
    }

    /**
     * Extracts the raw amount string from a currency expression.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function parseCurrencyAmount($value)
    {
        return static::parseCurrencyString($value)->amount;
    }

    /**
     * Determines how many decimal digits are present in the amount.
     *
     * @param string $value
     *
     * @return int|null
     */
    public static function parseCurrencyDecimals($value)
    {
        return static::parseCurrencyString($value)->decimals;
    }

    /**
     * Extracts the decimal separator character used in the amount.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function parseCurrencyDecimalSeparator($value)
    {
        return static::parseCurrencyString($value)->decimalSeparator;
    }

    /**
     * Extracts the thousands separator character used in the amount.
     *
     * @param string $value
     *
     * @return string|null
     */
    public static function parseCurrencyThousandsSeparator($value)
    {
        return static::parseCurrencyString($value)->thousandsSeparator;
    }

    /**
     * Parse a locale string into a Locale value object.
     *
     * @param string|null $value
     *
     * @return Locale
     */
    public static function parseLocale($value)
    {
        return Locale::from($value);
    }

    /**
     * Parse a locale string into a country code.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    public static function parseCountry($value)
    {
        return static::parseLocale($value)->country;
    }

    /**
     * Parse a language string into a Locale value object.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    public static function parseLanguage($value)
    {
        return static::parseLocale($value)->language;
    }

    /**
     * Return normalized locale string (e.g. "tr-TR").
     *
     * @param string|null $value
     *
     * @return string
     */
    public static function parseLocaleString($value)
    {
        return (string) static::parseLocale($value);
    }
}
