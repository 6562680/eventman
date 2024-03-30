<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Event\DemoEvent;
use Gzhegow\Eventman\Filter\DemoFilter;
use Gzhegow\Eventman\Handler\DemoEventHandler;
use Gzhegow\Eventman\Handler\DemoFilterHandler;


class DemoSubscriber implements SubscriberInterface
{
    public function demoEvent($event, $context) : void
    {
        echo __METHOD__ . PHP_EOL;
    }

    public function demoFilter($filter, $input, $context)
    {
        echo __METHOD__ . PHP_EOL;

        return $input;
    }


    public function eventHandlers() : array
    {
        return [
            [ DemoEvent::class, [ $this, 'demoEvent' ] ],
            [ DemoEvent::class, DemoEventHandler::class ],
        ];
    }

    public function filterHandlers() : array
    {
        return [
            [ DemoFilter::class, [ $this, 'demoFilter' ] ],
            [ DemoFilter::class, DemoFilterHandler::class ],
        ];
    }


    public static function events() : array
    {
        return [
            DemoEvent::class => true,
        ];
    }

    public static function filters() : array
    {
        return [
            DemoFilter::class => true,
        ];
    }
}
