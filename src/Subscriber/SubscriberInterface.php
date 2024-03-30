<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Filter\FilterInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


interface SubscriberInterface
{
    /**
     * @return array{0: string|EventInterface, 1: string|EventHandlerInterface}[]
     */
    public function eventHandlers() : array;

    /**
     * @return array{0: string|FilterInterface, 1: string|FilterHandlerInterface}[]
     */
    public function filterHandlers() : array;


    /**
     * @return array<class-string<EventInterface>,bool>
     */
    public static function events() : array;

    /**
     * @return array<class-string<FilterInterface>,bool>
     */
    public static function filters() : array;
}
