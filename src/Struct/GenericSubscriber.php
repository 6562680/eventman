<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Interfaces\HasNameInterface;
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


    public function getName() : string
    {
        if ($this->subscriberClass) {
            return $this->subscriberClass;
        }

        if ($this->subscriber) {
            if ($this->subscriber instanceof HasNameInterface) {
                return $this->subscriber->getName();
            }

            return get_class($this->subscriber);
        }

        throw new \RuntimeException('Unable to extract `name` from subscriber');
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
