<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Subscriber\SubscriberInterface;
use function Gzhegow\Eventman\_php_dump;


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


    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function from($subscriber, $context = null)
    {
        if (is_a($subscriber, GenericSubscriber::class)) {
            return $subscriber;
        }

        $_subscriber = null;
        $_subscriberClass = null;
        if (is_object($subscriber)) {
            if ($subscriber instanceof SubscriberInterface) {
                $_subscriber = $subscriber;
                $_subscriberClass = get_class($subscriber);
            }

        } elseif (is_string($subscriber) && ('' !== $subscriber)) {
            if (is_subclass_of($subscriber, SubscriberInterface::class)) {
                $_subscriberClass = $subscriber;
            }
        }

        $parsed = null
            ?? $_subscriber
            ?? $_subscriberClass;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _php_dump($subscriber)
            );
        }

        $generic = new GenericSubscriber();
        $generic->subscriber = $_subscriber;
        $generic->subscriberClass = $_subscriberClass;
        $generic->context = $context;

        return $generic;
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


    public function getPoints() : array
    {
        $pointList = $this->subscriberClass::points();

        return $pointList;
    }


    public function getContext()
    {
        return $this->context;
    }
}
