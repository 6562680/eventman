<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;


interface MiddlewareInterface
{
    /**
     * @param string|EventInterface|GenericEvent $event
     * @param Pipeline                           $pipeline
     * @param mixed|null                         $input
     * @param mixed|null                         $context
     *
     * @return mixed
     */
    public function handle($event, Pipeline $pipeline, $input = null, $context = null); // : mixed
}
