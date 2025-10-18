<?php

namespace Larawise\Localify\Events\Timezone;

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
class TimezoneCreated extends Event
{
    /**
     * Create a new event instance.
     *
     * @param string $timezone The timezone code.
     * @param array $payload The timezone payload.
     *
     * @return void
     */
    public function __construct(
        public string $timezone,
        public array $payload
    ) { }
}
