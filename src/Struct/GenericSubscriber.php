<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Subscriber\SubscriberInterface;
use Gzhegow\Eventman\Subscriber\EventSubscriberInterface;
use Gzhegow\Eventman\Subscriber\FilterSubscriberInterface;
use Gzhegow\Eventman\Subscriber\MiddlewareSubscriberInterface;


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


    public function getEventList() : array
    {
        $subscriber = $this->subscriber ?? $this->subscriberClass;

        if (! (
            is_a($subscriber, EventSubscriberInterface::class, true)
            || is_a($subscriber, MiddlewareSubscriberInterface::class, true)
        )) {
            return [];
        }

        $eventList = $subscriber::eventList();

        return $eventList;
    }

    public function getFilterList() : array
    {
        $subscriber = $this->subscriber ?? $this->subscriberClass;

        if (! (
            is_a($subscriber, FilterSubscriberInterface::class, true)
            || is_a($subscriber, MiddlewareSubscriberInterface::class, true)
        )) {
            return [];
        }

        $filterList = $subscriber::filterList();

        return $filterList;
    }


    public function getContext()
    {
        return $this->context;
    }
}
