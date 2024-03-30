<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


interface EventmanFactoryInterface
{
    public function newHandler(GenericHandler $handler) : callable;

    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface;


    public function callHandler(GenericHandler $handler, array $arguments = null);
}
