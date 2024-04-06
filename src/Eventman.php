<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;
use Gzhegow\Eventman\Subscriber\EventSubscriberInterface;
use Gzhegow\Eventman\Subscriber\FilterSubscriberInterface;
use Gzhegow\Eventman\Subscriber\MiddlewareSubscriberInterface;


class Eventman implements EventmanInterface
{
    /**
     * @var EventmanFactory
     */
    protected $factory;

    /**
     * @var array<int, GenericHandler|GenericMiddleware|GenericSubscriber>
     */
    protected $queue = [];
    /**
     * @var array<string, array<int, bool>
     */
    protected $taggedQueue = [];

    /**
     * @var array<int, callable>
     */
    protected $handlerCallables = [];
    /**
     * @var array<int, SubscriberInterface>
     */
    protected $subscriberInstances = [];

    /**
     * @var Pipeline
     */
    protected $pipeline;


    public function __construct(EventmanFactoryInterface $factory)
    {
        $this->factory = $factory;
    }


    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     * @param callable|EventHandlerInterface|GenericHandler      $handler
     *
     * @return void
     */
    public function onEvent($event, $handler) : void
    {
        $_event = $this->factory->assertEvent($event);
        $_handler = $this->factory->assertHandler($handler);

        $this->queue[] = $_handler;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ 'event' ][ $idx ] = true;

        $eventPoint = $this->factory->assertEventPoint($_event);

        $this->taggedQueue[ $eventPoint ][ $idx ] = true;
    }

    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     * @param callable|FilterHandlerInterface|GenericHandler     $handler
     *
     * @return void
     */
    public function onFilter($event, $handler) : void
    {
        $_event = $this->factory->assertEvent($event);
        $_handler = $this->factory->assertHandler($handler);

        $this->queue[] = $_handler;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ 'filter' ][ $idx ] = true;

        $eventPoint = $this->factory->assertEventPoint($_event);

        $this->taggedQueue[ $eventPoint ][ $idx ] = true;
    }


    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     * @param callable|MiddlewareInterface|GenericMiddleware     $middleware
     *
     * @return void
     */
    public function middleEvent($event, $middleware) : void
    {
        $_event = $this->factory->assertEvent($event);
        $_middleware = $this->factory->assertMiddleware($middleware);

        $this->queue[] = $_middleware;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ 'event' ][ $idx ] = true;

        $eventPoint = $this->factory->assertEventPoint($_event);

        $this->taggedQueue[ $eventPoint ][ $idx ] = true;
    }

    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     * @param callable|MiddlewareInterface|GenericMiddleware     $middleware
     *
     * @return void
     */
    public function middleFilter($event, $middleware) : void
    {
        $_event = $this->factory->assertEvent($event);
        $_middleware = $this->factory->assertMiddleware($middleware);

        $this->queue[] = $_middleware;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ 'filter' ][ $idx ] = true;

        $eventPoint = $this->factory->assertEventPoint($_event);

        $this->taggedQueue[ $eventPoint ][ $idx ] = true;
    }


    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     *
     * @return void
     */
    public function subscribe($subscriber) : void
    {
        $_subscriber = $this->factory->assertSubscriber($subscriber);

        $events = $_subscriber->getEventList();
        $filters = $_subscriber->getFilterList();

        $eventPoints = [];
        foreach ( $events as $i => $eventPoint ) {
            $_eventPoint = is_int($i)
                ? $eventPoint
                : $i;

            _assert_strlen($_eventPoint);

            $eventPoints[ $_eventPoint ] = true;
        }

        $filterPoints = [];
        foreach ( $filters as $i => $filterPoint ) {
            $_filterPoint = is_int($i)
                ? $filterPoint
                : $i;

            _assert_strlen($_filterPoint);

            $filterPoints[ $_filterPoint ] = true;
        }

        foreach ( $eventPoints as $eventPoint => $bool ) {
            $this->queue[] = $_subscriber;

            $idx = _array_key_last($this->queue);

            $this->taggedQueue[ 'event' ][ $idx ] = true;
            $this->taggedQueue[ $eventPoint ][ $idx ] = true;
        }

        foreach ( $filterPoints as $filterPoint => $bool ) {
            $this->queue[] = $_subscriber;

            $idx = _array_key_last($this->queue);

            $this->taggedQueue[ 'filter' ][ $idx ] = true;
            $this->taggedQueue[ $filterPoint ][ $idx ] = true;
        }
    }


    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $input
     * @param mixed|null                         $context
     *
     * @return void
     */
    public function fireEvent($event, $input = null, $context = null) : void
    {
        [ $middlewares, $handlers ] = $this->matchEvent('event', $event);

        $callables = [];
        foreach ( $handlers as $i => $handler ) {
            if (! isset($this->handlerCallables[ $i ])) {
                $callable = $this->factory->newHandlerCallable($handler);

                $this->handlerCallables[ $i ] = $callable;
            }

            $callables[ $i ] = $this->handlerCallables[ $i ];
        }

        if (! $middlewares) {
            foreach ( $callables ?? [] as $callable ) {
                $this->factory->call($callable, [ $event, $input, $context ]);
            }

            return;
        }

        $pipeline = $this->factory->newPipeline($middlewares);

        if (! $callables) {
            $nullMiddleware = function (
                $event, Pipeline $pipeline,
                $input = null
            ) {
                return $input;
            };

            $_middleware = $this->factory->assertMiddleware($nullMiddleware);

        } else {
            $callablesMiddleware = function (
                $event, Pipeline $pipeline,
                $input = null, $context = null
            ) use ($callables) {
                foreach ( $callables ?? [] as $callable ) {
                    $this->factory->call($callable, [ $event, $input, $context ]);
                }
            };

            $_middleware = $this->factory->assertMiddleware($callablesMiddleware);
        }

        $pipeline->addMiddleware($_middleware);

        $pipeline->run($event, $input, $context);
    }

    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed                              $input
     * @param mixed|null                         $context
     *
     * @return mixed
     */
    public function applyFilter($event, $input, $context = null) // : mixed
    {
        [ $middlewares, $handlers ] = $this->matchEvent('filter', $event);

        $callables = [];
        foreach ( $handlers as $i => $handler ) {
            if (! isset($this->handlerCallables[ $i ])) {
                $callable = $this->factory->newHandlerCallable($handler);

                $this->handlerCallables[ $i ] = $callable;
            }

            $callables[ $i ] = $this->handlerCallables[ $i ];
        }

        $current = $input;

        if (! $middlewares) {
            foreach ( $callables ?? [] as $callable ) {
                $current = $this->factory->call($callable, [ $event, $current, $context ]);
            }

            return $current;
        }

        $pipeline = $this->factory->newPipeline($middlewares);

        if (! $callables) {
            $nullMiddleware = function (
                $event, Pipeline $pipeline,
                $input = null
            ) {
                return $input;
            };

            $_middleware = $this->factory->assertMiddleware($nullMiddleware);

        } else {
            $callablesMiddleware = function (
                $event, Pipeline $pipeline,
                $input = null, $context = null
            ) use ($callables) {
                $current = $input;

                foreach ( $callables ?? [] as $callable ) {
                    $current = $this->factory->call($callable, [ $event, $current, $context ]);
                }

                return $current;
            };

            $_middleware = $this->factory->assertMiddleware($callablesMiddleware);
        }

        $pipeline->addMiddleware($_middleware);

        $current = $pipeline->run($event, $current, $context);

        return $current;
    }


    /**
     * @param string                             $eventType
     * @param string|EventInterface|GenericEvent $event
     *
     * @return array{0: GenericMiddleware[], 1: GenericHandler[]}
     */
    public function matchEvent(string $eventType, $event) : array
    {
        if (! isset(static::LIST_EVENT_TYPE[ $eventType ])) {
            throw new \RuntimeException(
                'Unknown `eventType`: ' . $eventType
            );
        }

        $_event = $this->factory->assertEvent($event);

        $eventPoint = $this->factory->assertEventPoint($_event);

        $index = array_intersect_key(
            $this->taggedQueue[ $eventPoint ] ?? [],
            $this->taggedQueue[ $eventType ] ?? []
        );

        $middlewares = [];
        $handlers = [];
        foreach ( $index as $i => $bool ) {
            $handlerGroup = $this->queue[ $i ];

            if ($handlerGroup instanceof GenericHandler) {
                $key = $i;

                $handlers[ $key ] = $handlerGroup;

            } elseif ($handlerGroup instanceof GenericMiddleware) {
                $key = $i;

                $middlewares[ $key ] = $handlerGroup;

            } elseif ($handlerGroup instanceof GenericSubscriber) {
                if (! isset($this->subscriberInstances[ $i ])) {
                    $this->subscriberInstances[ $i ] = $this->factory->newSubscriber($handlerGroup);
                }

                $subscriberObject = $this->subscriberInstances[ $i ];

                $mapEventType = [
                    static::EVENT_TYPE_EVENT  => [ EventSubscriberInterface::class, 'events' ],
                    static::EVENT_TYPE_FILTER => [ FilterSubscriberInterface::class, 'filters' ],
                ];

                [ $interface, $objectMethodName ] = $mapEventType[ $eventType ];

                if ($subscriberObject instanceof $interface) {
                    foreach ( $subscriberObject->{$objectMethodName}() as $ii => [ $sEventPoint, $sHandler ] ) {
                        if ($sEventPoint !== $eventPoint) {
                            continue;
                        }

                        $key = "{$i}.{$ii}";

                        $_sHandler = $this->factory->assertHandler($sHandler);

                        $handlers[ $key ] = $_sHandler;
                    }
                }

                if ($subscriberObject instanceof MiddlewareSubscriberInterface) {
                    foreach ( $subscriberObject->middlewares() as $ii => [ $sEventPoint, $sMiddleware ] ) {
                        if ($sEventPoint !== $eventPoint) {
                            continue;
                        }

                        $key = "{$i}.{$ii}";

                        $_sMiddleware = $this->factory->assertMiddleware($sMiddleware);

                        $middlewares[ $key ] = $_sMiddleware;
                    }
                }
            }
        }

        return [ $middlewares, $handlers ];
    }
}
