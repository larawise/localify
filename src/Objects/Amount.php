<?php

namespace Larawise\Localify\Objects;

use Illuminate\Support\Str;
use Larawise\Localify\Enums\Currency;
use Stringable;

final class Amount implements Stringable
{
    /**
     * Raw extracted amount string (e.g. "1.234,56").
     *
     * @var string|null
     */
    public $amount;

    /**
     * ISO 4217 currency code (e.g. "USD", "EUR", "TRY").
     *
     * @var string|null
     */
    public $currency;

    /**
     * ISO 4217 currency code (e.g. "USD", "EUR", "TRY").
     *
     * @var string|null
     */
    public $symbol;

    /**
     * Indicates the display direction based on currency placement.
     *
     * @var string|null
     */
    public $direction;

    /**
     * Number of decimal digits detected in the amount.
     *
     * @var int|null
     */
    public $decimals;

    /**
     * Character used to separate decimal digits (e.g. "," or ".").
     *
     * @var string|null
     */
    public $decimalSeparator;

    /**
     * Character used to group thousands (e.g. "." or ",").
     *
     * @var string|null
     */
    public $thousandsSeparator;

    /**
     * Original raw currency string before normalization.
     *
     * @var mixed
     */
    public $original;

    /**
     * Internal cache for parsed currency strings.
     *
     * @var array<string, self>
     */
    private static $cache = [];

    /**
     * Regex pattern used to parse currency strings.
     *
     * @var string
     */
    private static $regex = '/^(?:(?<currency_left>[A-Z]{3}|[^\d\s.,]+)\s*)?(?<amount>\d{1,3}(?:[.,\s\'`]?\d{3})*(?:[.,]\d+)?)(?:\s*(?<currency_right>[A-Z]{3}|[^\d\s.,]+))?$/u';

    /**
     * Create a new currency instance.
     *
     * @param string|null $currency
     * @param string|null $amount
     * @param string|null $direction
     * @param string|null $original
     * @param string|null $decimalSeparator
     * @param string|null $thousandsSeparator
     * @param int|null $decimals
     *
     * @return void
     */
    public function __construct($currency, $amount, $direction, $original = null, $decimalSeparator = null, $thousandsSeparator = null, $decimals = null)
    {
        $this->currency = $currency ? strtoupper($currency) : null;
        $this->amount = $amount ? Str::replace(',', '.', $amount) : null;
        $this->direction = $direction ? strtoupper($direction) : null;
        $this->original = $original ?? '';
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;
        $this->decimals = $decimals;
    }

    /**
     * Create a currency object from a raw string using regex parsing.
     *
     * @param string|null $value
     * @param string|null $regex
     *
     * @return static
     */
    public static function from($value, $regex = null)
    {
        // Normalize input: trim whitespace and remove internal spaces
        $normalized = Str::of($value)->trim()->replace([' '], '')->toString();

        // Generate a unique cache key based on normalized input and optional regex
        $key = md5(($regex ?? '') . '|' . $normalized);

        // Return cached result if available
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        // If input is blank, return an empty Currency object
        if (blank($normalized)) {
            return self::$cache[$key] = new static(null, null, null, $value);
        }

        // Use provided regex or fallback to default pattern
        $pattern = $regex ?? Amount::$regex;

        // Apply regex to extract currency and amount components
        preg_match($pattern, $normalized, $matches);

        // Extract left and right currency codes, trimming any whitespace
        $currency_left  = trim($matches['currency_left'] ?? '');
        $currency_right = trim($matches['currency_right'] ?? '');

        // Determine direction based on currency position
        $direction = $currency_left ? 'LTR' : ($currency_right ? 'RTL' : null);

        // Determine raw currency input (could be ISO or symbol)
        $rawCurrency = $currency_left ?: ($currency_right ?: null);
        $currency = null;

        // Try resolving symbol to ISO code via enum
        if ($rawCurrency) {
            $resolved = Currency::fromSymbol($rawCurrency);
            $currency = $resolved ? $resolved->value : (
                Str::length($rawCurrency) === 3 ? strtoupper($rawCurrency) : null
            );
        }

        // Extract numeric amount
        $amount = $matches['amount'] ?? null;

        // Analyze separators and decimal precision
        [$decimalSeparator, $thousandsSeparator, $decimals] = self::ensureSeparators($amount);

        // Create and cache the Currency instance
        return self::$cache[$key] = new Amount(
            $currency,
            $amount,
            $direction,
            $value,
            $decimalSeparator,
            $thousandsSeparator,
            $decimals
        );
    }

    /**
     * Convert the currency to string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if (! $this->currency || ! $this->amount) {
            return '';
        }

        return $this->direction === 'RTL'
            ? "{$this->amount} {$this->currency}"
            : "{$this->currency} {$this->amount}";
    }

    /**
     * Check if both language and country are null.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->currency) && empty($this->amount);
    }

    /**
     * Check if original string matches normalized output.
     *
     * @return bool
     */
    public function isNormalized()
    {
        return Str::replace(',', '.', Str::of($this->original)->trim()) === (string) $this;
    }

    /**
     * Detects formatting separators used in a currency amount string.
     *
     * @param string|null $amount
     *
     * @return array
     */
    private static function ensureSeparators($amount)
    {
        $decimalSeparator = null;
        $thousandsSeparator = null;
        $decimals = null;

        if (preg_match('/(\d{1,3})([.,\s\'`])(\d{3})([.,])(\d+)/', $amount ?? '', $m)) {
            $thousandsSeparator = $m[2];
            $decimalSeparator = $m[4];
            $decimals = strlen($m[5]);
        } elseif (preg_match('/(\d+)([.,])(\d+)/', $amount ?? '', $m)) {
            $decimalSeparator = $m[2];
            $decimals = strlen($m[3]);
        }

        return [$decimalSeparator, $thousandsSeparator, $decimals];
    }
}

