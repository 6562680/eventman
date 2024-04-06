<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Event\DemoEvent;
use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Handler\DemoMiddleware;
use Gzhegow\Eventman\Handler\DemoEventHandler;
use Gzhegow\Eventman\Handler\DemoFilterHandler;


class DemoSubscriber implements
    EventSubscriberInterface,
    FilterSubscriberInterface,
    MiddlewareSubscriberInterface
{
    public function demoEvent($event, $input = null, $context = null) : void
    {
        echo __METHOD__ . PHP_EOL;
    }

    public function demoFilter($event, $input, $context = null)
    {
        echo __METHOD__ . PHP_EOL;

        return $input;
    }

    public function demoMiddleware($event, Pipeline $pipeline, $input = null, $context = null)
    {
        echo __METHOD__ . '@before' . PHP_EOL;

        $result = $pipeline->next($event, $input, $context);

        echo __METHOD__ . '@after' . PHP_EOL;

        return $result;
    }


    public function events() : array
    {
        return [
            [ DemoEvent::class, [ $this, 'demoEvent' ] ],
            [ DemoEvent::class, DemoEventHandler::class ],
        ];
    }

    public function filters() : array
    {
        return [
            [ DemoEvent::class, [ $this, 'demoFilter' ] ],
            [ DemoEvent::class, DemoFilterHandler::class ],
        ];
    }

    public function middlewares() : array
    {
        return [
            [ DemoEvent::class, [ $this, 'demoMiddleware' ] ],
            [ DemoEvent::class, DemoMiddleware::class ],
        ];
    }


    public static function eventList() : array
    {
        return [
            DemoEvent::class,
            DemoEvent::class => true,
        ];
    }

    public static function filterList() : array
    {
        return [
            DemoEvent::class,
            DemoEvent::class => true,
        ];
    }
}
