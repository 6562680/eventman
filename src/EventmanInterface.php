<?php

namespace Gzhegow\Eventman;


use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericPoint;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


interface EventmanInterface
{
    const TASK_TYPE_EVENT  = 'event';
    const TASK_TYPE_FILTER = 'filter';

    const LIST_TASK_TYPE = [
        self::TASK_TYPE_EVENT  => true,
        self::TASK_TYPE_FILTER => true,
    ];


    /**
     * @template-covariant T of callable|EventHandlerInterface|GenericHandler
     * @template-covariant TT of callable|MiddlewareInterface|GenericMiddleware
     *
     * @param T|T[]        $handlers
     * @param TT|TT[]|null $middlewares
     *
     * @return Pipeline
     */
    public function pipelineEvent($handlers, $middlewares = null) : Pipeline;

    /**
     * @template-covariant T of callable|EventHandlerInterface|GenericHandler
     * @template-covariant TT of callable|MiddlewareInterface|GenericMiddleware
     *
     * @param T|T[]        $handlers
     * @param TT|TT[]|null $middlewares
     *
     * @return Pipeline
     */
    public function pipelineFilter($handlers, $middlewares = null) : Pipeline;


    /**
     * @param string|GenericPoint                           $point
     * @param callable|EventHandlerInterface|GenericHandler $handler
     *
     * @return void
     */
    public function onEvent($point, $handler) : void;

    /**
     * @param string|GenericPoint                            $point
     * @param callable|FilterHandlerInterface|GenericHandler $handler
     *
     * @return void
     */
    public function onFilter($point, $handler) : void;


    /**
     * @param string|GenericPoint                            $point
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     *
     * @return void
     */
    public function middleEvent($point, $middleware) : void;

    /**
     * @param string|GenericPoint                            $point
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     *
     * @return void
     */
    public function middleFilter($point, $middleware) : void;


    /**
     * @param SubscriberInterface|GenericSubscriber $subscriber
     *
     * @return void
     */
    public function subscribe($subscriber) : void;


    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return void
     */
    public function fireEvent($point, $input = null, $context = null) : void;

    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return mixed
     */
    public function applyFilter($point, $input = null, $context = null);


    /**
     * @param string                    $taskType
     * @param string|GenericPoint|array $points
     *
     * @return array{0: GenericMiddleware[], 1: GenericHandler[]}
     */
    public function task(string $taskType, $points) : array;
}
