<?php

namespace Larawise\Localify\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

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
class Money implements ValidationRule
{
    public function __construct(
        public bool $nullable = true,
        public ?int $min = null,
        public ?int $max = null
    ) { }

    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var string $defaultCurrency */
        $defaultCurrency = config('money.default_currency');
        $money = MoneyParser::parse($value, $defaultCurrency);

        if (!$this->nullable && $money === null) {
            $fail('localify::validation.money')->translate();
        }

        if ($money) {
            if ($this->min !== null && $money->isLessThan($this->min)) {
                $fail('localify::validation.money_min')->translate([
                    'value' => $this->min,
                ]);
            }

            if ($this->max !== null && $money->isGreaterThan($this->max)) {
                $fail('localify::validation.money_max')->translate([
                    'value' => $this->max,
                ]);
            }
        }
    }
}
