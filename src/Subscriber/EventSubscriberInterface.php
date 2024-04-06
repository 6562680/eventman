<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Handler\EventHandlerInterface;


interface EventSubscriberInterface extends SubscriberInterface
{
    /**
     * @return array{0: string, 1: callable|EventHandlerInterface|GenericHandler}[]
     */
    public function events() : array;


    /**
     * @return array<string, bool>
     */
    public static function eventList() : array;
}
