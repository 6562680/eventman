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
    public function newHandlerCallable(GenericHandler $handler) : callable
    {
        return null
            ?? $this->newHandlerCallableByCallable($handler)
            ?? $this->newHandlerCallableByEventHandler($handler)
            ?? $this->newHandlerCallableByFilterHandler($handler)
            ?? $this->newHandlerCallableByInvokableClass($handler)
            ?? $this->newHandlerCallableByPublicMethod($handler);
    }

    protected function newHandlerCallableByCallable(GenericHandler $handler) : ?callable
    {
        if (! $handler->callable) return null;

        return $handler->callable;
    }

    protected function newHandlerCallableByEventHandler(GenericHandler $handler) : ?callable
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

    protected function newHandlerCallableByFilterHandler(GenericHandler $handler) : ?callable
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

    protected function newHandlerCallableByInvokableClass(GenericHandler $handler) : ?callable
    {
        if (! $handler->invokableClass) return null;

        $class = $handler->getInvokableClass();

        return new $class();
    }

    protected function newHandlerCallableByPublicMethod(GenericHandler $handler) : ?callable
    {
        if (! $handler->publicMethod) return null;

        [ $class, $method ] = $handler->publicMethod;

        return [ new $class(), $method ];
    }


    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface
    {
        $class = $subscriber->getSubscriberClass();

        return new $class();
    }


    public function call(callable $fn, array $arguments = null)
    {
        $arguments = $arguments ?? [];

        $result = call_user_func_array($fn, $arguments);

        return $result;
    }


    public function assertEvent($event, $context = null) : GenericEvent
    {
        if (is_a($event, GenericEvent::class)) {
            return $event;
        }

        $_event = null;
        $_eventClass = null;
        $_eventObject = null;
        $_eventObjectClass = null;
        $_eventClassString = null;
        $_eventString = null;
        if (is_object($event)) {
            $class = get_class($event);

            if ($event instanceof EventInterface) {
                $_event = $event;
                $_eventClass = $class;

            } else {
                $_eventObject = $event;
                $_eventObjectClass = $class;
            }

        } elseif (null !== ($_event = _filter_strlen($event))) {
            if (class_exists($_event)) {
                if (is_subclass_of($_event, FilterInterface::class)) {
                    throw new \LogicException(
                        "The `event` must not be subclass of: " . FilterInterface::class
                    );
                }

                if (is_subclass_of($_event, EventInterface::class)) {
                    $_eventClass = $_event;

                } else {
                    $_eventClassString = $_event;
                }

            } else {
                $_eventString = $_event;
            }
        }

        $parsed = null
            ?? $_event
            ?? $_eventClass
            ?? $_eventObject
            ?? $_eventClassString
            ?? $_eventString;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _assert_dump($event)
            );
        }

        $generic = new GenericEvent();
        $generic->event = $_event;
        $generic->eventClass = $_eventClass;
        $generic->eventObject = $_eventObject;
        $generic->eventObjectClass = $_eventObjectClass;
        $generic->eventClassString = $_eventClassString;
        $generic->eventString = $_eventString;
        $generic->context = $context;

        return $generic;
    }

    public function assertFilter($filter, $context = null) : GenericFilter
    {
        if (is_a($filter, GenericFilter::class)) {
            return $filter;
        }

        $_filter = null;
        $_filterClass = null;
        $_filterObject = null;
        $_filterObjectClass = null;
        $_filterClassString = null;
        $_filterString = null;
        if (is_object($filter)) {
            $class = get_class($filter);

            if ($filter instanceof FilterInterface) {
                $_filter = $filter;
                $_filterClass = $class;

            } else {
                $_filterObject = $filter;
                $_filterObjectClass = $class;
            }

        } elseif (null !== ($_filter = _filter_strlen($filter))) {
            if (class_exists($_filter)) {
                if (is_subclass_of($_filter, EventInterface::class)) {
                    throw new \LogicException(
                        "The `filter` must not be subclass of: " . EventInterface::class
                    );
                }

                if (is_subclass_of($_filter, FilterInterface::class)) {
                    $_filterClass = $_filter;

                } else {
                    $_filterClassString = $_filter;
                }

            } else {
                $_filterString = $_filter;
            }
        }

        $parsed = null
            ?? $_filter
            ?? $_filterClass
            ?? $_filterObject
            ?? $_filterClassString
            ?? $_filterString;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _assert_dump($filter)
            );
        }

        $generic = new GenericFilter();
        $generic->filter = $_filter;
        $generic->filterClass = $_filterClass;
        $generic->filterObject = $_filterObject;
        $generic->filterObjectClass = $_filterObjectClass;
        $generic->filterClassString = $_filterClassString;
        $generic->filterString = $_filterString;
        $generic->context = $context;

        return $generic;
    }

    public function assertHandler($handler, $context = null) : GenericHandler
    {
        if (is_a($handler, GenericHandler::class)) {
            return $handler;
        }

        $_eventHandler = null;
        $_eventHandlerClass = null;
        $_filterHandler = null;
        $_filterHandlerClass = null;
        $_callable = null;
        $_invokableClass = null;
        $_publicMethod = null;
        if (is_object($handler)) {
            if ($handler instanceof EventHandlerInterface) {
                $_eventHandler = $handler;
                $_eventHandlerClass = get_class($handler);

            } elseif ($handler instanceof FilterHandlerInterface) {
                $_filterHandler = $handler;
                $_filterHandlerClass = get_class($handler);

            } elseif (is_callable($handler)) {
                $_callable = $handler;
            }

        } elseif (is_array($handler)) {
            if (is_callable($handler)) {
                $_callable = $handler;

            } elseif (null !== ($_handler = _filter_method($handler))) {
                $_publicMethod = $_handler;
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

        $parsed = null
            ?? $_eventHandler
            ?? $_eventHandlerClass
            ?? $_filterHandler
            ?? $_filterHandlerClass
            ?? $_callable
            ?? $_invokableClass
            ?? $_publicMethod;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _assert_dump($handler)
            );
        }

        $generic = new GenericHandler();
        $generic->eventHandler = $_eventHandler;
        $generic->eventHandlerClass = $_eventHandlerClass;
        $generic->filterHandler = $_filterHandler;
        $generic->filterHandlerClass = $_filterHandlerClass;
        $generic->callable = $_callable;
        $generic->invokableClass = $_invokableClass;
        $generic->publicMethod = $_publicMethod;
        $generic->context = $context;

        return $generic;
    }

    public function assertSubscriber($subscriber, $context = null) : GenericSubscriber
    {
        if (is_a($subscriber, GenericSubscriber::class)) {
            return $subscriber;
        }

        $_subscriber = null;
        $_subscriberClass = null;
        if (is_object($subscriber)) {
            if ($subscriber instanceof SubscriberInterface) {
                $_subscriber = $subscriber;
            }

        } elseif (is_string($subscriber) && ('' !== $subscriber)) {
            if (is_subclass_of($subscriber, SubscriberInterface::class)) {
                $_subscriberClass = $subscriber;
            }
        }

        $parsed = null
            ?? $_subscriber
            ?? $_subscriberClass;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _assert_dump($subscriber)
            );
        }

        $generic = new GenericSubscriber();
        $generic->subscriber = $_subscriber;
        $generic->subscriberClass = $_subscriberClass;
        $generic->context = $context;

        return $generic;
    }
}
