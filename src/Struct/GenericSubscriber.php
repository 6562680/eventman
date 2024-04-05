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

    /**
     * @var mixed
     */
    public $context;


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


    public function getContext()
    {
        return $this->context;
    }
}
