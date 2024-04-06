<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;


interface FilterHandlerInterface
{
    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed                              $input
     * @param mixed|null                         $context
     *
     * @return mixed
     */
    public function handle($event, $input, $context = null); // : mixed
}
