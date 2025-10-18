<?php

namespace Larawise\Localify\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Larawise\Localify\Objects\Timezone;

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
class CurrencyCast implements CastsAttributes
{
    /**
     * Cast the given value into a Timezone object.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     *
     * @return Timezone|null
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return $value ? Timezone::from($value) : null;
    }

    /**
     * Prepare the timezone object for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param Timezone|string|null $value
     * @param array $attributes
     *
     * @return string|null
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if ($value instanceof Timezone) {
            return $value->timezone();
        }

        return is_string($value) ? Timezone::from($value)?->timezone() : null;
    }
}
