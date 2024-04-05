<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Filter\FilterInterface;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


class Eventman implements EventmanInterface
{
    /**
     * @var EventmanFactory
     */
    protected $factory;

    /**
     * @var array<int, GenericHandler|GenericSubscriber>
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


    public function __construct(EventmanFactoryInterface $factory)
    {
        $this->factory = $factory;
    }


    /**
     * @param string|class-string<EventInterface>|EventInterface                 $event
     * @param callable|class-string<EventHandlerInterface>|EventHandlerInterface $handler
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

        $eventType = null
            ?? $_event->eventClass
            ?? $_event->eventObjectClass
            ?? $_event->eventClassString
            ?? $_event->eventString;

        $this->taggedQueue[ $eventType ][ $idx ] = true;
    }


    /**
     * @param string|class-string<FilterInterface>|FilterInterface                 $filter
     * @param callable|class-string<FilterHandlerInterface>|FilterHandlerInterface $handler
     *
     * @return void
     */
    public function onFilter($filter, $handler) : void
    {
        $_filter = $this->factory->assertFilter($filter);
        $_handler = $this->factory->assertHandler($handler);

        $this->queue[] = $_handler;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ 'filter' ][ $idx ] = true;

        $filterType = null
            ?? $_filter->filterClass
            ?? $_filter->filterObjectClass
            ?? $_filter->filterClassString
            ?? $_filter->filterString;

        $this->taggedQueue[ $filterType ][ $idx ] = true;
    }


    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     *
     * @return void
     */
    public function subscribe($subscriber) : void
    {
        $_subscriber = $this->factory->assertSubscriber($subscriber);

        $events = $_subscriber->getEvents();
        $filters = $_subscriber->getFilters();

        foreach ( $events as $eventType => $bool ) {
            $this->queue[] = $_subscriber;

            $idx = _array_key_last($this->queue);

            $this->taggedQueue[ 'event' ][ $idx ] = true;
            $this->taggedQueue[ $eventType ][ $idx ] = true;
        }

        foreach ( $filters as $filterType => $bool ) {
            $this->queue[] = $_subscriber;

            $idx = _array_key_last($this->queue);

            $this->taggedQueue[ 'filter' ][ $idx ] = true;
            $this->taggedQueue[ $filterType ][ $idx ] = true;
        }
    }


    public function fireEvent($event, $context = null) : void
    {
        $_event = $this->factory->assertEvent($event);

        $eventType = null
            ?? $_event->eventClass
            ?? $_event->eventObjectClass
            ?? $_event->eventClassString
            ?? $_event->eventString;

        $index = array_intersect_key(
            $this->taggedQueue[ $eventType ] ?? [],
            $this->taggedQueue[ 'event' ] ?? []
        );

        $callables = [];
        foreach ( $index as $i => $bool ) {
            $handlerGroup = $this->queue[ $i ];

            if ($handlerGroup instanceof GenericHandler) {
                $key = $i;

                if (! isset($this->handlerCallables[ $key ])) {
                    $this->handlerCallables[ $key ] = $this->factory->newHandlerCallable($handlerGroup);
                }

                $callables[ $key ] = $this->handlerCallables[ $key ];

            } elseif ($handlerGroup instanceof GenericSubscriber) {
                if (! isset($this->subscriberInstances[ $i ])) {
                    $this->subscriberInstances[ $i ] = $this->factory->newSubscriber($handlerGroup);
                }

                $subscriberObject = $this->subscriberInstances[ $i ];

                foreach ( $subscriberObject->eventHandlers() as $ii => [ $sEventType, $sHandler ] ) {
                    if ($sEventType !== $eventType) {
                        continue;
                    }

                    $key = "{$i}.{$ii}";

                    if (! isset($this->handlerCallables[ $key ])) {
                        $_sHandler = $this->factory->assertHandler($sHandler);

                        $this->handlerCallables[ $key ] = $this->factory->newHandlerCallable($_sHandler);
                    }

                    $callables[ $key ] = $this->handlerCallables[ $key ];
                }
            }
        }

        foreach ( $callables ?? [] as $callable ) {
            $this->factory->call($callable, [ $event, $context ]);
        }
    }

    public function applyFilter($filter, $input, $context = null) // : mixed
    {
        $_filter = $this->factory->assertFilter($filter);

        $filterType = null
            ?? $_filter->filterClass
            ?? $_filter->filterObjectClass
            ?? $_filter->filterClassString
            ?? $_filter->filterString;

        $index = array_intersect_key(
            $this->taggedQueue[ $filterType ] ?? [],
            $this->taggedQueue[ 'filter' ] ?? []
        );

        $callables = [];
        foreach ( $index as $i => $bool ) {
            $handlerGroup = $this->queue[ $i ];

            if ($handlerGroup instanceof GenericHandler) {
                $key = $i;

                if (! isset($this->handlerCallables[ $key ])) {
                    $this->handlerCallables[ $key ] = $this->factory->newHandlerCallable($handlerGroup);
                }

                $callables[ $key ] = $this->handlerCallables[ $key ];

            } elseif ($handlerGroup instanceof GenericSubscriber) {
                if (! isset($this->subscriberInstances[ $i ])) {
                    $this->subscriberInstances[ $i ] = $this->factory->newSubscriber($handlerGroup);
                }

                $subscriberObject = $this->subscriberInstances[ $i ];

                foreach ( $subscriberObject->filterHandlers() as $ii => [ $sFilterType, $sHandler ] ) {
                    if ($sFilterType !== $filterType) {
                        continue;
                    }

                    $key = "{$i}.{$ii}";

                    if (! isset($this->handlerCallables[ $key ])) {
                        $_sHandler = $this->factory->assertHandler($sHandler);

                        $this->handlerCallables[ $key ] = $this->factory->newHandlerCallable($_sHandler);
                    }

                    $callables[ $key ] = $this->handlerCallables[ $key ];
                }
            }
        }

        $current = $input;

        foreach ( $callables ?? [] as $callable ) {
            $current = $this->factory->call($callable, [ $filter, $current, $context ]);
        }

        return $current;
    }
}
