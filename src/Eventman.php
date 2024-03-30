<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Struct\GenericFilter;
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
        $_event = $this->assertEvent($event);

        $eventName = (string) $_event;

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
                    $subscriberName = (string) $subscriber;
                    $subscriberObject = $this->subscribers[ $subscriberName ] ?? null;

                    if (is_bool($subscriberObject)) {
                        $subscriberObject = $this->factory->newSubscriber($subscriber);

                        $this->subscribers[ $subscriberName ] = $subscriberObject;
                    }

                    foreach ( $subscriberObject->eventHandlers() as [ $subscriberEventName, $handler ] ) {
                        if ($subscriberEventName !== $eventName) {
                            continue;
                        }

                        $_handler = $this->assertHandler($handler);

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
        $_filter = $this->assertFilter($filter);

        $filterName = (string) $_filter;

        $handlers = $this->events[ $filterName ] ?? null;

        if (is_null($handlers)) {
            foreach ( $this->registryFilters as $idx => [ $registryEventName, $arguments ] ) {
                if ($registryEventName !== $filterName) {
                    continue;
                }

                [ $argument ] = $arguments;

                if ($argument instanceof GenericHandler) {
                    $handlers[] = $argument;

                } elseif ($argument instanceof GenericSubscriber) {
                    $subscriber = $argument;
                    $subscriberName = (string) $subscriber;
                    $subscriberObject = $this->subscribers[ $subscriberName ] ?? null;

                    if (is_bool($subscriberObject)) {
                        $subscriberObject = $this->factory->newSubscriber($subscriber);

                        $this->subscribers[ $subscriberName ] = $subscriberObject;
                    }

                    foreach ( $subscriberObject->filterHandlers() as [ $subscriberEventName, $handler ] ) {
                        if ($subscriberEventName !== $filterName) continue;

                        $_handler = $this->assertHandler($handler);

                        $handlers[] = $_handler;
                    }
                }

                unset($this->registryEvents[ $idx ]);
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
        $_event = $this->assertEvent($event);
        $_handler = $this->assertHandler($handler);

        $eventName = (string) $_event;

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
        $_event = $this->assertEvent($event);

        $_handler = null;
        if ($handler) {
            $_handler = $this->assertHandler($handler);
        }

        $eventName = (string) $_event;

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
        $_filter = $this->assertFilter($filter);
        $_handler = $this->assertHandler($handler);

        $filterName = (string) $_filter;

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
        $_filter = $this->assertFilter($filter);

        $_handler = null;
        if ($handler) {
            $_handler = $this->assertHandler($handler);
        }

        $filterName = (string) $_filter;

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
        $_subscriber = $this->assertSubscriber($subscriber);

        $subscriberName = (string) $_subscriber;

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
        $_subscriber = $this->assertSubscriber($subscriber);

        $subscriberKey = (string) $_subscriber;

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


    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     */
    protected function assertEvent($event) : GenericEvent
    {
        $instance = null;

        $_name = null;
        $_event = null;
        $_eventClass = null;
        if ($event instanceof EventInterface) {
            $_event = $event;

        } elseif (is_string($event) && ('' !== $event)) {
            if (class_exists($event)) {
                if (is_subclass_of($event, EventInterface::class)) {
                    $_eventClass = $event;

                } elseif (is_subclass_of($event, FilterInterface::class)) {
                    throw new \LogicException(
                        "The `event` must not be subclass of: " . FilterInterface::class
                    );

                }

            } else {
                $_name = $event;
            }
        }

        if ($_name || $_event || $_eventClass) {
            $instance = new GenericEvent();
            $instance->name = $_name;
            $instance->event = $_event;
            $instance->eventClass = $_eventClass;
        }

        if (! $instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($event)
            );
        }

        return $instance;
    }

    /**
     * @param string|class-string<FilterInterface>|FilterInterface $filter
     */
    protected function assertFilter($filter) : GenericFilter
    {
        $instance = null;

        $_name = null;
        $_filter = null;
        $_filterClass = null;
        if ($filter instanceof FilterInterface) {
            $_filter = $filter;

        } elseif (is_string($filter) && ('' !== $filter)) {
            if (class_exists($filter)) {
                if (is_subclass_of($filter, FilterInterface::class)) {
                    $_filterClass = $filter;

                } elseif (is_subclass_of($filter, EventInterface::class)) {
                    throw new \LogicException(
                        "The `filter` must not be subclass of: " . EventInterface::class
                    );
                }

            } else {
                $_name = $filter;
            }
        }

        if ($_name || $_filter || $_filterClass) {
            $instance = new GenericFilter();
            $instance->name = $_name;
            $instance->filter = $_filter;
            $instance->filterClass = $_filterClass;
        }

        if (! $instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($filter)
            );
        }

        return $instance;
    }

    /**
     * @param callable|class-string<EventHandlerInterface|FilterHandlerInterface>|EventHandlerInterface|FilterHandlerInterface $handler
     */
    protected function assertHandler($handler) : GenericHandler
    {
        $instance = null;

        $_callable = null;
        $_invokableClass = null;
        $_eventHandler = null;
        $_eventHandlerClass = null;
        $_filterHandler = null;
        $_filterHandlerClass = null;
        if (is_object($handler)) {
            if ($handler instanceof EventHandlerInterface) {
                $_eventHandler = $handler;

            } elseif ($handler instanceof FilterHandlerInterface) {
                $_filterHandler = $handler;

            } elseif (is_callable($handler)) {
                $_callable = $handler;
            }

        } elseif (is_array($handler)) {
            if (is_callable($handler)) {
                $_callable = $handler;
            }

        } else {
            if (is_string($handler) && ('' !== $handler)) {
                if (class_exists($handler)) {
                    if (is_subclass_of($handler, EventHandlerInterface::class)) {
                        $_eventHandlerClass = $handler;

                    } elseif (is_subclass_of($handler, FilterHandlerInterface::class)) {
                        $_filterHandlerClass = $handler;

                    } elseif (method_exists($handler, '__invoke')) {
                        $_invokableClass = $handler;
                    }

                } elseif (is_callable($handler)) {
                    $_callable = $handler;
                }
            }
        }

        if ($_callable
            || $_eventHandler
            || $_eventHandlerClass
            || $_filterHandler
            || $_filterHandlerClass
            || $_invokableClass
        ) {
            $instance = new GenericHandler();
            $instance->callable = $_callable;
            $instance->eventHandler = $_eventHandler;
            $instance->eventHandlerClass = $_eventHandlerClass;
            $instance->filterHandler = $_filterHandler;
            $instance->filterHandlerClass = $_filterHandlerClass;
            $instance->invokableCLass = $_invokableClass;
        }

        if (! $instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($handler)
            );
        }

        return $instance;
    }

    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     */
    protected function assertSubscriber($subscriber) : GenericSubscriber
    {
        $instance = null;

        $_subscriber = null;
        $_subscriberClass = null;
        if ($subscriber instanceof SubscriberInterface) {
            $_subscriber = $subscriber;

        } elseif (is_string($subscriber) && ('' !== $subscriber)) {
            if (is_subclass_of($subscriber, SubscriberInterface::class)) {
                $_subscriberClass = $subscriber;
            }
        }

        if ($_subscriber || $_subscriberClass) {
            $instance = new GenericSubscriber();
            $instance->subscriber = $_subscriber;
            $instance->subscriberClass = $_subscriberClass;
        }

        if (! $instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($subscriber)
            );
        }

        return $instance;
    }
}
