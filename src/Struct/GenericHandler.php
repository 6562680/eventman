<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


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
