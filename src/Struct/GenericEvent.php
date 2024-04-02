<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Interfaces\HasNameInterface;


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
     * @var string
     */
    public $eventString;

    /**
     * @var mixed
     */
    public $context;


    public function getName() : string
    {
        if ($this->eventClass) {
            return $this->eventClass;
        }

        if ($this->eventString) {
            return $this->eventString;
        }

        if ($this->event) {
            if ($this->event instanceof HasNameInterface) {
                return $this->event->getName();
            }

            return get_class($this->event);
        }

        throw new \RuntimeException('Unable to extract `name` from event');
    }


    public function isSame(GenericEvent $event) : bool
    {
        return ($this->eventString && ($this->eventString === $event->eventString))
            || ($this->event && ($this->event === $event->event))
            || ($this->eventClass && ($this->eventClass === $event->eventClass));
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

    public function getEventString() : string
    {
        return $this->eventString;
    }


    public function getContext()
    {
        return $this->context;
    }
}
