<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericPoint;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


interface EventmanParserInterface
{
    /**
     * @param GenericPoint $point
     *
     * @return array<string, bool>
     */
    public function parseEventPoints(GenericPoint $point) : array;


    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $context
     */
    public function assertPoint($point, $context = null) : GenericPoint;

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
}
