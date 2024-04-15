<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Point\DemoPoint;
use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Handler\DemoMiddleware;
use Gzhegow\Eventman\Handler\DemoEventHandler;
use Gzhegow\Eventman\Handler\DemoFilterHandler;


class DemoSubscriber implements
    MiddlewareSubscriberInterface,
    //
    EventSubscriberInterface,
    FilterSubscriberInterface
{
    public function demoMiddleware(Pipeline $pipeline, $point, $input = null, $context = null)
    {
        echo __METHOD__ . '@before' . PHP_EOL;

        $result = $pipeline->next($point, $input, $context);

        echo __METHOD__ . '@after' . PHP_EOL;

        return $result;
    }


    public function demoEvent($point, $input = null, $context = null) : void
    {
        echo __METHOD__ . PHP_EOL;
    }

    public function demoFilter($point, $input = null, $context = null)
    {
        echo __METHOD__ . PHP_EOL;

        return $input;
    }


    public function middlewares() : array
    {
        return [
            [ DemoPoint::class, [ $this, 'demoMiddleware' ] ],
            [ DemoPoint::class, DemoMiddleware::class ],
        ];
    }


    public function events() : array
    {
        return [
            [ DemoPoint::class, [ $this, 'demoEvent' ] ],
            [ DemoPoint::class, DemoEventHandler::class ],
        ];
    }

    public function filters() : array
    {
        return [
            [ DemoPoint::class, [ $this, 'demoFilter' ] ],
            [ DemoPoint::class, DemoFilterHandler::class ],
        ];
    }


    public static function points() : array
    {
        return [
            // > можно задать ключами (чтобы проверить себя на уникальность) или значениями
            // DemoPoint::class,
            // DemoPoint::class => true,

            DemoPoint::class => true,
        ];
    }
}
