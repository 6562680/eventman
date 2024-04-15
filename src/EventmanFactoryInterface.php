<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


interface EventmanFactoryInterface
{
    public function newParser() : EventmanParserInterface;


    public function newHandlerCallable(GenericHandler $handler); // : callable;

    public function newMiddlewareCallable(GenericMiddleware $middleware); // : callable;


    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface;


    public function newPipeline(EventmanProcessorInterface $processor) : Pipeline;
}
