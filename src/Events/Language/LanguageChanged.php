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
class LanguageChanged extends Event
{
    /**
     * Create a new event instance.
     *
     * @param string $locale The locale code.
     * @param bool $fallback The locale fallback.
     *
     * @return void
     */
    public function __construct(
        public string $locale,
        public bool $fallback = false
    ) { }
}
