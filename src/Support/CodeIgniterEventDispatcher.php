<?php
/**
 * This file is part of Vima PHP.
 *
 * (c) Vima PHP <https://github.com/lipex-org/vima-core>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vima\CodeIgniter\Support;

use Vima\Core\Events\Contracts\EventDispatcherInterface;
use CodeIgniter\Events\Events;

/**
 * Class CodeIgniterEventDispatcher
 * 
 * Bridges Vima core events to CodeIgniter 4's event system.
 *
 * @package Vima\CodeIgniter\Support
 */
class CodeIgniterEventDispatcher implements EventDispatcherInterface
{
    /**
     * Dispatch an event to CI4 Events.
     * 
     * The event name in CI4 will be the fully qualified class name of the event object.
     *
     * @param object $event
     * @return object
     */
    public function dispatch(object $event): object
    {
        $name = method_exists($event, 'getName') ? $event->getName() : get_class($event);

        Events::trigger($name, $event);

        // Also trigger the class name for backward compatibility or more specific listeners
        if ($name !== get_class($event)) {
            Events::trigger(get_class($event), $event);
        }

        // Also fire a generic "vima.event" for easier global catching
        Events::trigger('vima.event', $event);

        return $event;
    }
}
