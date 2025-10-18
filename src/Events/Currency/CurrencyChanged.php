<?php

namespace Larawise\Localify\Events\Language;

use Larawise\Events\Event;

/**
 * Srylius - The ultimate symphony for technology architecture!
 *
 * @package     Srylius
 * @subpackage  Core
 * @version     v8.0.0
 * @author      Selçuk Çukur <hk@selcukcukur.com.tr>
 * @copyright   Srylius Teknoloji Limited Şirketi
 *
 * @see https://docs.srylius.com/ Srylius : Dev
 */
class CurrencyChanged extends Event
{
    /**
     * Create a new event instance.
     *
     * @param string $currency The currency code.
     * @param bool $fallback The currency fallback.
     *
     * @return void
     */
    public function __construct(
        public string $currency,
        public bool $fallback = false
    ) { }
}
