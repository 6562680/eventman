<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


class GenericHandler
{
    /**
     * @var callable
     */
    public $callable;
    /**
     * @var class-string
     */
    public $invokableClass;

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


    public function isSame(GenericHandler $handler) : bool
    {
        return ($this->callable && ($this->callable === $handler->callable))
            || ($this->eventHandler && ($this->eventHandler === $handler->eventHandler))
            || ($this->filterHandler && ($this->filterHandler === $handler->filterHandler))
            || ($this->eventHandlerClass && ($this->eventHandlerClass === $handler->eventHandlerClass))
            || ($this->filterHandlerClass && ($this->filterHandlerClass === $handler->filterHandlerClass))
            || ($this->invokableClass && ($this->invokableClass === $handler->invokableClass));
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
}
