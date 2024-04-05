<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Struct\GenericFilter;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


interface EventmanFactoryInterface
{
    public function newHandlerCallable(GenericHandler $handler) : callable;

    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface;


    public function call(callable $fn, array $arguments = null);


    public function assertEvent($event, $context = null) : GenericEvent;

    public function assertFilter($filter, $context = null) : GenericFilter;

    public function assertHandler($handler, $context = null) : GenericHandler;

    public function assertSubscriber($subscriber, $context = null) : GenericSubscriber;
}
