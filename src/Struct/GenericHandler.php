<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Handler\HandlerInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;
use function Gzhegow\Eventman\_php_dump;
use function Gzhegow\Eventman\_filter_method;


class GenericHandler
{
    /**
     * @var EventHandlerInterface
     */
    public $eventHandler;
    /**
     * @var class-string<EventHandlerInterface>|EventHandlerInterface
     */
    public $eventHandlerClass;

    /**
     * @var FilterHandlerInterface
     */
    public $filterHandler;
    /**
     * @var class-string<FilterHandlerInterface>|FilterHandlerInterface
     */
    public $filterHandlerClass;

    /**
     * @var HandlerInterface
     */
    public $handler;
    /**
     * @var class-string<HandlerInterface>|HandlerInterface
     */
    public $handlerClass;

    /**
     * @var callable
     */
    public $callable;

    /**
     * @var class-string
     */
    public $invokableClass;

    /**
     * @var array|callable
     */
    public $publicMethod;

    /**
     * @var mixed
     */
    public $context;


    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function from($handler, $context = null)
    {
        if (is_a($handler, GenericHandler::class)) {
            return $handler;
        }

        $_eventHandler = null;
        $_eventHandlerClass = null;
        $_filterHandler = null;
        $_filterHandlerClass = null;
        $_handler = null;
        $_handlerClass = null;
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

            } elseif ($handler instanceof HandlerInterface) {
                $_handler = $handler;
                $_handlerClass = get_class($handler);

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
            ?? $_handler
            ?? $_handlerClass
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
        $generic->handler = $_handler;
        $generic->handlerClass = $_handlerClass;
        $generic->callable = $_callable;
        $generic->invokableClass = $_invokableClass;
        $generic->publicMethod = $_publicMethod;
        $generic->context = $context;

        return $generic;
    }


    public function getEventHandler() : EventHandlerInterface
    {
        return $this->eventHandler;
    }

    /**
     * @return class-string<EventHandlerInterface>
     */
    public function getEventHandlerClass() : string
    {
        return $this->eventHandlerClass;
    }


    public function getFilterHandler() : FilterHandlerInterface
    {
        return $this->filterHandler;
    }

    /**
     * @return class-string<FilterHandlerInterface>
     */
    public function getFilterHandlerClass() : string
    {
        return $this->filterHandlerClass;
    }


    public function getHandler() : HandlerInterface
    {
        return $this->handler;
    }

    /**
     * @return class-string<HandlerInterface>
     */
    public function getHandlerClass() : string
    {
        return $this->handlerClass;
    }


    public function getCallable() : callable
    {
        return $this->callable;
    }


    /**
     * @return class-string
     */
    public function getInvokableClass() : string
    {
        return $this->invokableClass;
    }


    /**
     * @return array|callable
     */
    public function getPublicMethod() : array
    {
        return $this->publicMethod;
    }


    public function getContext()
    {
        return $this->context;
    }
}
