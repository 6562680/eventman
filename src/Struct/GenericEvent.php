<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Event\EventInterface;


class GenericEvent
{
    /**
     * @var EventInterface
     */
    public $event;
    /**
     * @var class-string<EventInterface>|EventInterface
     */
    public $eventClass;

    /**
     * @var object
     */
    public $eventObject;
    /**
     * @var class-string
     */
    public $eventObjectClass;

    /**
     * @var class-string
     */
    public $eventClassString;

    /**
     * @var string
     */
    public $eventString;

    /**
     * @var mixed
     */
    public $context;


    public function getEvent() : EventInterface
    {
        return $this->event;
    }

    /**
     * @return class-string<EventInterface>|EventInterface
     */
    public function getEventClass() : string
    {
        return $this->eventClass;
    }


    public function getEventObject() : object
    {
        return $this->eventObject;
    }

    /**
     * @return class-string
     */
    public function getEventObjectClass() : string
    {
        return $this->eventObjectClass;
    }


    /**
     * @return class-string
     */
    public function getEventClassString() : string
    {
        return $this->eventClassString;
    }


    public function getEventString() : string
    {
        return $this->eventString;
    }


    public function getContext()
    {
        return $this->context;
    }
}
