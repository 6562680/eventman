<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


interface EventmanFactoryInterface
{
    public function newHandlerCallable(GenericHandler $handler) : callable;

    public function newMiddlewareCallable(GenericMiddleware $middleware) : callable;


    /**
     * @param GenericMiddleware[] $middlewares
     */
    public function newPipeline(array $middlewares) : Pipeline;

    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface;


    public function call(callable $fn, array $arguments = null);


    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $context
     */
    public function assertEvent($event, $context = null) : GenericEvent;

    /**
     * @param callable|EventHandlerInterface|FilterHandlerInterface|GenericHandler $handler
     * @param mixed|null                                                           $context
     */
    public function assertHandler($handler, $context = null) : GenericHandler;

    /**
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     * @param mixed|null                                     $context
     */
    public function assertMiddleware($middleware, $context = null) : GenericMiddleware;

    /**
     * @param SubscriberInterface|GenericSubscriber $subscriber
     * @param mixed|null                            $context
     */
    public function assertSubscriber($subscriber, $context = null) : GenericSubscriber;


    public function assertEventPoint(GenericEvent $event) : string;
}
