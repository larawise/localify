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
class LanguageDeleted extends Event
{
    /**
     * Create a new event instance.
     *
     * @param string $locale The language code.
     * @param array $payload The language payload.
     *
     * @return void
     */
    public function __construct(
        public string $locale,
        public array $payload
    ) { }
}
