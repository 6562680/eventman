<?php

namespace Gzhegow\Eventman;


use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


interface EventmanInterface
{
    const EVENT_TYPE_EVENT  = 'event';
    const EVENT_TYPE_FILTER = 'filter';

    const LIST_EVENT_TYPE = [
        self::EVENT_TYPE_EVENT  => true,
        self::EVENT_TYPE_FILTER => true,
    ];


    /**
     * @param string|EventInterface|GenericEvent            $event
     * @param callable|EventHandlerInterface|GenericHandler $handler
     *
     * @return void
     */
    public function onEvent($event, $handler) : void;

    /**
     * @param string|EventInterface|GenericEvent             $event
     * @param callable|FilterHandlerInterface|GenericHandler $handler
     *
     * @return void
     */
    public function onFilter($event, $handler) : void;


    /**
     * @param string|EventInterface|GenericEvent             $event
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     *
     * @return void
     */
    public function middleEvent($event, $middleware) : void;

    /**
     * @param string|EventInterface|GenericEvent             $event
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     *
     * @return void
     */
    public function middleFilter($event, $middleware) : void;


    /**
     * @param SubscriberInterface|GenericSubscriber $subscriber
     *
     * @return void
     */
    public function subscribe($subscriber) : void;


    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $input
     * @param mixed|null                         $context
     *
     * @return void
     */
    public function fireEvent($event, $input = null, $context = null) : void;

    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed                              $input
     * @param mixed|null                         $context
     *
     * @return mixed
     */
    public function applyFilter($event, $input, $context = null);


    /**
     * @param string                             $eventType
     * @param string|EventInterface|GenericEvent $event
     *
     * @return array{0: GenericMiddleware[], 1: GenericHandler[]}
     */
    public function matchEvent(string $eventType, $event) : array;
}
