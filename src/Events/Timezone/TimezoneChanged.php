<?php

namespace Larawise\Localify\Events\Timezone;

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
class TimezoneChanged
{
    /**
     * Create a new event instance.
     *
     * @param string $timezone The timezone code.
     * @param bool $fallback The timezone fallback.
     *
     * @return void
     */
    public function __construct(
        public string $timezone,
        public bool $fallback = false
    ) { }
}
