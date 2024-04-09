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


    public function newMiddlewareCallable(GenericMiddleware $middleware) : callable
    {
        return null
            ?? $this->newMiddlewareCallableByCallable($middleware)
            ?? $this->newMiddlewareCallableByMiddleware($middleware)
            ?? $this->newMiddlewareCallableByInvokableClass($middleware)
            ?? $this->newMiddlewareCallableByPublicMethod($middleware);
    }

    protected function newMiddlewareCallableByCallable(GenericMiddleware $middleware) : ?callable
    {
        if (! $middleware->callable) return null;

        return $middleware->callable;
    }

    protected function newMiddlewareCallableByMiddleware(GenericMiddleware $middleware) : ?callable
    {
        if ($middleware->middleware) {
            return [ $middleware->middleware, 'handle' ];
        }

        if ($middleware->middlewareClass) {
            $class = $middleware->middlewareClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    protected function newMiddlewareCallableByInvokableClass(GenericMiddleware $middleware) : ?callable
    {
        if (! $middleware->invokableClass) return null;

        $class = $middleware->getInvokableClass();

        return new $class();
    }

    protected function newMiddlewareCallableByPublicMethod(GenericMiddleware $middleware) : ?callable
    {
        if (! $middleware->publicMethod) return null;

        [ $class, $method ] = $middleware->publicMethod;

        return [ new $class(), $method ];
    }


    /**
     * @param GenericMiddleware[] $middlewares
     */
    public function newPipeline(array $middlewares) : Pipeline
    {
        $pipeline = new Pipeline($this, $middlewares);

        return $pipeline;
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


    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $context
     */
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
                . _php_dump($event)
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

    /**
     * @param callable|EventHandlerInterface|FilterHandlerInterface|GenericHandler $handler
     * @param mixed|null                                                           $context
     */
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

            } elseif (($_handler = _filter_method($handler, [ true ], $rm)) && $rm->isPublic()) {
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
                . _php_dump($handler)
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

    /**
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     * @param mixed|null                                     $context
     */
    public function assertMiddleware($middleware, $context = null) : GenericMiddleware
    {
        if (is_a($middleware, GenericMiddleware::class)) {
            return $middleware;
        }

        $_middleware = null;
        $_middlewareClass = null;
        $_callable = null;
        $_invokableClass = null;
        $_publicMethod = null;
        if (is_object($middleware)) {
            if ($middleware instanceof MiddlewareInterface) {
                $_middleware = $middleware;
                $_middlewareClass = get_class($middleware);

            } elseif (is_callable($middleware)) {
                $_callable = $middleware;
            }

        } elseif (is_array($middleware)) {
            if (is_callable($middleware)) {
                $_callable = $middleware;

            } elseif (($_middleware = _filter_method($middleware, [ true ], $rm)) && $rm->isPublic()) {
                $_publicMethod = $_middleware;
            }

        } else {
            if (is_string($middleware) && ('' !== $middleware)) {
                if (class_exists($middleware)) {
                    if (is_subclass_of($middleware, MiddlewareInterface::class)) {
                        $_middlewareClass = $middleware;

                    } elseif (method_exists($middleware, '__invoke')) {
                        $_invokableClass = $middleware;
                    }

                } elseif (is_callable($middleware)) {
                    $_callable = $middleware;
                }
            }
        }

        $parsed = null
            ?? $_middleware
            ?? $_middlewareClass
            ?? $_callable
            ?? $_invokableClass
            ?? $_publicMethod;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _php_dump($middleware)
            );
        }

        $generic = new GenericMiddleware();
        $generic->middleware = $_middleware;
        $generic->middlewareClass = $_middlewareClass;
        $generic->callable = $_callable;
        $generic->invokableClass = $_invokableClass;
        $generic->publicMethod = $_publicMethod;
        $generic->context = $context;

        return $generic;
    }

    /**
     * @param SubscriberInterface|GenericSubscriber $subscriber
     * @param mixed|null                            $context
     */
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
                . _php_dump($subscriber)
            );
        }

        $generic = new GenericSubscriber();
        $generic->subscriber = $_subscriber;
        $generic->subscriberClass = $_subscriberClass;
        $generic->context = $context;

        return $generic;
    }


    public function assertEventPoint(GenericEvent $event) : string
    {
        $eventType = null
            ?? $event->eventClass
            ?? $event->eventObjectClass
            ?? $event->eventClassString
            ?? $event->eventString;

        return $eventType;
    }
}
