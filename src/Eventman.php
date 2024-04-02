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
     * @var EventmanFactoryInterface
     */
    protected $factory;

    /**
     * @var array<int, array{0: string, 1: array}>
     */
    protected $registryEvents = [];
    /**
     * @var array<int, array{0: string, 1: array}>
     */
    protected $registryFilters = [];

    /**
     * @var array<string, int>
     */
    protected $registryEventsIndexBySubscriber = [];
    /**
     * @var array<string, int>
     */
    protected $registryFiltersIndexBySubscriber = [];

    /**
     * @var array<string, array<int, GenericHandler>>
     */
    protected $events = [];
    /**
     * @var array<string, array<int, GenericHandler>>
     */
    protected $filters = [];

    /**
     * @var array<bool|SubscriberInterface>
     */
    protected $subscribers = [];


    public function __construct(
        EventmanFactoryInterface $factory
    )
    {
        $this->factory = $factory;
    }


    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     * @param mixed                                              $context
     *
     * @return void
     */
    public function fireEvent($event, $context = null) : void
    {
        $_event = $this->factory->assertEvent($event);

        $eventName = $_event->getName();

        $handlers = $this->events[ $eventName ] ?? null;

        if (is_null($handlers)) {
            foreach ( $this->registryEvents as $idx => [ $registryEventName, $arguments ] ) {
                if ($registryEventName !== $eventName) {
                    continue;
                }

                [ $argument ] = $arguments;

                if ($argument instanceof GenericHandler) {
                    $handlers[] = $argument;

                } elseif ($argument instanceof GenericSubscriber) {
                    $subscriber = $argument;
                    $subscriberName = $subscriber->getName();
                    $subscriberObject = $this->subscribers[ $subscriberName ] ?? null;

                    if (is_bool($subscriberObject)) {
                        $subscriberObject = $this->factory->newSubscriber($subscriber);

                        $this->subscribers[ $subscriberName ] = $subscriberObject;
                    }

                    foreach ( $subscriberObject->eventHandlers() as [ $subscriberEventName, $handler ] ) {
                        if ($subscriberEventName !== $eventName) {
                            continue;
                        }

                        $_handler = $this->factory->assertHandler($handler);

                        $handlers[] = $_handler;
                    }
                }

                unset($this->registryEvents[ $idx ]);
            }

            if ($handlers) {
                foreach ( $handlers as $idx => $handler ) {
                    $this->events[ $eventName ][ $idx ] = $handler;
                }
            }
        }

        foreach ( $this->events[ $eventName ] ?? [] as $handler ) {
            $this->factory->callHandler(
                $handler,
                [ $event, $context ]
            );
        }
    }

    /**
     * @param string|class-string<FilterInterface>|FilterInterface $filter
     * @param mixed                                                $context
     *
     * @return mixed
     */
    public function fireFilter($filter, $input, $context = null) // : mixed
    {
        $_filter = $this->factory->assertFilter($filter);

        $filterName = $_filter->getName();

        $handlers = $this->filters[ $filterName ] ?? null;

        if (is_null($handlers)) {
            foreach ( $this->registryFilters as $idx => [ $registryFilterName, $arguments ] ) {
                if ($registryFilterName !== $filterName) {
                    continue;
                }

                [ $argument ] = $arguments;

                if ($argument instanceof GenericHandler) {
                    $handlers[] = $argument;

                } elseif ($argument instanceof GenericSubscriber) {
                    $subscriber = $argument;
                    $subscriberName = $subscriber->getName();
                    $subscriberObject = $this->subscribers[ $subscriberName ] ?? null;

                    if (is_bool($subscriberObject)) {
                        $subscriberObject = $this->factory->newSubscriber($subscriber);

                        $this->subscribers[ $subscriberName ] = $subscriberObject;
                    }

                    foreach ( $subscriberObject->filterHandlers() as [ $subscriberFilterName, $handler ] ) {
                        if ($subscriberFilterName !== $filterName) continue;

                        $_handler = $this->factory->assertHandler($handler);

                        $handlers[] = $_handler;
                    }
                }

                unset($this->registryFilters[ $idx ]);
            }

            if ($handlers) {
                foreach ( $handlers as $idx => $handler ) {
                    $this->filters[ $filterName ][ $idx ] = $handler;
                }
            }
        }

        $current = $input;

        foreach ( $this->filters[ $filterName ] ?? [] as $handler ) {
            $current = $this->factory->callHandler(
                $handler,
                [ $filter, $current, $context ]
            );
        }

        return $current;
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

        $eventName = $_event->getName();

        $this->registryEvents[] = [ $eventName, [ $_handler, $_event ] ];
    }

    /**
     * @param string|class-string<EventInterface>|EventInterface                 $event
     * @param callable|class-string<EventHandlerInterface>|EventHandlerInterface $handler
     *
     * @return void
     */
    public function offEvent($event, $handler = null) : void
    {
        $_event = $this->factory->assertEvent($event);

        $_handler = null;
        if ($handler) {
            $_handler = $this->factory->assertHandler($handler);
        }

        $eventName = $_event->getName();

        foreach ( $this->registryEvents as $idx => [ $registryEventName ] ) {
            if ($registryEventName === $eventName) {
                unset($this->registryEvents[ $idx ]);
            }
        }

        if (! $_handler) {
            unset($this->events[ $eventName ]);

        } else {
            foreach ( $this->events[ $eventName ] ?? [] as $idx => $eventHandler ) {
                if ($eventHandler->isSame($_handler)) {
                    unset($this->events[ $eventName ][ $idx ]);
                }
            }
        }
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

        $filterName = $_filter->getName();

        $this->registryFilters[] = [ $filterName, [ $_handler, $_filter ] ];
    }

    /**
     * @param string|class-string<FilterInterface>|FilterInterface                 $filter
     * @param callable|class-string<FilterHandlerInterface>|FilterHandlerInterface $handler
     *
     * @return void
     */
    public function offFilter($filter, $handler = null) : void
    {
        $_filter = $this->factory->assertFilter($filter);

        $_handler = null;
        if ($handler) {
            $_handler = $this->factory->assertHandler($handler);
        }

        $filterName = $_filter->getName();

        foreach ( $this->registryFilters as $idx => [ $registryFilterName ] ) {
            if ($registryFilterName === $filterName) {
                unset($this->registryFilters[ $idx ]);
            }
        }

        if (! $_handler) {
            unset($this->filters[ $filterName ]);

        } else {
            foreach ( $this->filters[ $filterName ] ?? [] as $idx => $filterHandler ) {
                if ($filterHandler->isSame($_handler)) {
                    unset($this->filters[ $filterName ][ $idx ]);
                }
            }
        }
    }


    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     *
     * @return void
     */
    public function subscribe($subscriber) : void
    {
        $_subscriber = $this->factory->assertSubscriber($subscriber);

        $subscriberName = $_subscriber->getName();

        if (isset($this->subscribers[ $subscriberName ])) {
            throw new \RuntimeException('Subscriber already registered: ' . _assert_dump($subscriber));
        }

        $this->subscribers[ $subscriberName ] = true;

        $events = $_subscriber->getEvents();
        $filters = $_subscriber->getFilters();

        foreach ( $events as $eventName => $bool ) {
            $this->registryEvents[] = [ $eventName, [ $_subscriber ] ];
            $this->registryEventsIndexBySubscriber[ $subscriberName ][] = _array_key_last($this->registryEvents);
        }

        foreach ( $filters as $filterName => $bool ) {
            $this->registryFilters[] = [ $filterName, [ $_subscriber ] ];
            $this->registryFiltersIndexBySubscriber[ $subscriberName ][] = _array_key_last($this->registryFilters);
        }
    }

    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     *
     * @return void
     */
    public function unsubscribe($subscriber) : void
    {
        $_subscriber = $this->factory->assertSubscriber($subscriber);

        $subscriberKey = $_subscriber->getName();

        foreach ( $this->registryEventsIndexBySubscriber[ $subscriberKey ] ?? [] as $idx ) {
            unset($this->registryEvents[ $idx ]);
        }

        foreach ( $this->registryFiltersIndexBySubscriber[ $subscriberKey ] ?? [] as $idx ) {
            unset($this->registryFilters[ $idx ]);
        }

        unset($this->registryEventsIndexBySubscriber[ $subscriberKey ]);
        unset($this->registryFiltersIndexBySubscriber[ $subscriberKey ]);

        unset($this->subscribers[ $subscriberKey ]);
    }
}
