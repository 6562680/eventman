<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Event\EventInterface;


class GenericEvent
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var EventInterface
     */
    public $event;
    /**
     * @var class-string<EventInterface>|EventInterface
     */
    public $eventClass;


    public function __toString()
    {
        return $this->eventClass
            ?? ($this->event ? get_class($this->event) : null)
            ?? $this->name;
    }


    public function isSame(GenericEvent $event) : bool
    {
        return ($this->name && ($this->name === $event->name))
            || ($this->event && ($this->event === $event->event))
            || ($this->eventClass && ($this->eventClass === $event->eventClass));
    }


    public function getName() : string
    {
        return $this->name;
    }


    public function getEvent() : EventInterface
    {
        return $this->event;
    }

    /**
     * @return class-string<EventInterface>
     */
    public function getEventClass() : string
    {
        return $this->eventClass;
    }
}
