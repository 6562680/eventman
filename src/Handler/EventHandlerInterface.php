<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;


interface EventHandlerInterface
{
    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $input
     * @param mixed|null                         $context
     *
     * @return void
     */
    public function handle($event, $input = null, $context = null) : void;
}
