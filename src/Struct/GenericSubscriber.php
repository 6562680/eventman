<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Subscriber\SubscriberInterface;


class GenericSubscriber
{
    /**
     * @var SubscriberInterface
     */
    public $subscriber;
    /**
     * @var class-string<SubscriberInterface>|SubscriberInterface
     */
    public $subscriberClass;


    public function __toString()
    {
        return $this->subscriberClass ??
            ($this->subscriber ? get_class($this->subscriber) : null);
    }


    public function isSame(GenericSubscriber $subscriber) : bool
    {
        return ($this->subscriber && ($this->subscriber === $subscriber->subscriber))
            || ($this->subscriberClass && ($this->subscriberClass === $subscriber->subscriberClass));
    }


    public function getSubscriber() : SubscriberInterface
    {
        return $this->subscriber;
    }

    /**
     * @return class-string<SubscriberInterface>
     */
    public function getSubscriberClass() : string
    {
        return $this->subscriberClass;
    }


    public function getEvents() : array
    {
        return null
            ?? ($this->subscriberClass ? $this->subscriberClass::events() : null)
            ?? ($this->subscriber ? $this->subscriber::events() : null)
            ?? [];
    }

    public function getFilters() : array
    {
        return null
            ?? ($this->subscriberClass ? $this->subscriberClass::filters() : null)
            ?? ($this->subscriber ? $this->subscriber::filters() : null)
            ?? [];
    }
}
