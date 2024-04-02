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


class EventmanFactory implements EventmanFactoryInterface
{
    public function newHandler(GenericHandler $handler) : callable
    {
        return null
            ?? $this->newHandlerCallable($handler)
            ?? $this->newHandlerEventHandler($handler)
            ?? $this->newHandlerFilterHandler($handler)
            ?? $this->newHandlerInvokableClass($handler);
    }

    private function newHandlerCallable(GenericHandler $handler) : ?callable
    {
        if (! $handler->callable) return null;

        return $handler->getCallable();
    }

    private function newHandlerEventHandler(GenericHandler $handler) : ?callable
    {
        if ($handler->eventHandler) {
            return [ $handler->eventHandler, 'handle' ];
        }

        if ($handler->eventHandlerClass) {
            $class = $handler->eventHandlerClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    private function newHandlerFilterHandler(GenericHandler $handler) : ?callable
    {
        if ($handler->filterHandler) {
            return [ $handler->filterHandler, 'handle' ];
        }

        if ($handler->filterHandlerClass) {
            $class = $handler->filterHandlerClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    private function newHandlerInvokableClass(GenericHandler $handler) : ?callable
    {
        if (! $handler->invokableClass) return null;

        $class = $handler->getInvokableClass();

        return new $class();
    }


    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface
    {
        $class = $subscriber->getSubscriberClass();

        return new $class();
    }


    public function callHandler(GenericHandler $handler, array $arguments = null)
    {
        $arguments = $arguments ?? [];

        $callable = $this->newHandler($handler);

        $result = call_user_func_array($callable, $arguments);

        return $result;
    }


    /**
     * @param string|class-string<EventInterface>|EventInterface $event
     */
    public function assertEvent($event, $context = null) : GenericEvent
    {
        $_instance = null;
        $_name = null;
        $_event = null;
        $_eventClass = null;
        if ($event instanceof GenericEvent) {
            $_instance = $event;

        } elseif ($event instanceof EventInterface) {
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

        if (! $_instance) {
            if ($_name || $_event || $_eventClass) {
                $_instance = new GenericEvent();
                $_instance->eventString = $_name;
                $_instance->event = $_event;
                $_instance->eventClass = $_eventClass;
            }
        }

        $_instance->context = $context;

        if (! $_instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($event)
            );
        }

        return $_instance;
    }

    /**
     * @param string|class-string<FilterInterface>|FilterInterface $filter
     */
    public function assertFilter($filter, $context = null) : GenericFilter
    {
        $_instance = null;
        $_name = null;
        $_filter = null;
        $_filterClass = null;
        if ($filter instanceof GenericFilter) {
            $_instance = $filter;

        } elseif ($filter instanceof FilterInterface) {
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

        if (! $_instance) {
            if ($_name || $_filter || $_filterClass) {
                $_instance = new GenericFilter();
                $_instance->filterString = $_name;
                $_instance->filter = $_filter;
                $_instance->filterClass = $_filterClass;
            }
        }

        $_instance->context = $context;

        if (! $_instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($filter)
            );
        }

        return $_instance;
    }

    /**
     * @param callable|class-string<EventHandlerInterface|FilterHandlerInterface>|EventHandlerInterface|FilterHandlerInterface $handler
     */
    public function assertHandler($handler, $context = null) : GenericHandler
    {
        $_instance = null;
        $_callable = null;
        $_invokableClass = null;
        $_eventHandler = null;
        $_eventHandlerClass = null;
        $_filterHandler = null;
        $_filterHandlerClass = null;
        if ($handler instanceof GenericHandler) {
            $_instance = $handler;

        } elseif (is_object($handler)) {
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

        if (! $_instance) {
            if ($_callable
                || $_eventHandler
                || $_eventHandlerClass
                || $_filterHandler
                || $_filterHandlerClass
                || $_invokableClass
            ) {
                $_instance = new GenericHandler();
                $_instance->callable = $_callable;
                $_instance->eventHandler = $_eventHandler;
                $_instance->eventHandlerClass = $_eventHandlerClass;
                $_instance->filterHandler = $_filterHandler;
                $_instance->filterHandlerClass = $_filterHandlerClass;
                $_instance->invokableClass = $_invokableClass;
            }
        }

        $_instance->context = $context;

        if (! $_instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($handler)
            );
        }

        return $_instance;
    }

    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     */
    public function assertSubscriber($subscriber, $context = null) : GenericSubscriber
    {
        $_instance = null;
        $_subscriber = null;
        $_subscriberClass = null;
        if ($subscriber instanceof GenericSubscriber) {
            $_instance = $subscriber;

        } elseif ($subscriber instanceof SubscriberInterface) {
            $_subscriber = $subscriber;

        } elseif (is_string($subscriber) && ('' !== $subscriber)) {
            if (is_subclass_of($subscriber, SubscriberInterface::class)) {
                $_subscriberClass = $subscriber;
            }
        }

        if (! $_instance) {
            if ($_subscriber || $_subscriberClass) {
                $_instance = new GenericSubscriber();
                $_instance->subscriber = $_subscriber;
                $_instance->subscriberClass = $_subscriberClass;
            }
        }

        $_instance->context = $context;

        if (! $_instance) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': ' . _assert_dump($subscriber)
            );
        }

        return $_instance;
    }
}
