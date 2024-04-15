<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericPoint;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;
use Gzhegow\Eventman\Subscriber\EventSubscriberInterface;
use Gzhegow\Eventman\Subscriber\FilterSubscriberInterface;
use Gzhegow\Eventman\Subscriber\MiddlewareSubscriberInterface;


class Eventman implements EventmanInterface
{
    /**
     * @var EventmanFactory
     */
    protected $factory;

    /**
     * @var EventmanParser
     */
    protected $parser;
    /**
     * @var EventmanProcessor
     */
    protected $processor;

    /**
     * @var array<int, GenericHandler|GenericMiddleware|GenericSubscriber>
     */
    protected $queue = [];
    /**
     * @var array<string, array<int, bool>
     */
    protected $taggedQueue = [];

    /**
     * @var array<int, SubscriberInterface>
     */
    protected $subscriberInstances = [];


    public function __construct(
        EventmanFactoryInterface $factory,
        //
        EventmanParserInterface $parser = null,
        EventmanProcessorInterface $processor = null
    )
    {
        $this->factory = $factory;

        $this->parser = $parser ?? $this->factory->newParser();
        $this->processor = $processor ?? $this->factory->newProcessor();
    }


    /**
     * @template-covariant T of callable|EventHandlerInterface|GenericHandler
     * @template-covariant TT of callable|MiddlewareInterface|GenericMiddleware
     *
     * @param T|T[]        $handlers
     * @param TT|TT[]|null $middlewares
     *
     * @return Pipeline
     */
    public function pipelineEvent($handlers, $middlewares = null) : Pipeline
    {
        $middlewares = $middlewares ?? [];

        $_handlers = _list($handlers, '\Gzhegow\Eventman\_filter_method');
        $_middlewares = _list($middlewares, '\Gzhegow\Eventman\_filter_method');

        $_handlers = array_map([ $this->parser, 'assertHandler' ], $_handlers);
        $_middlewares = array_map([ $this->parser, 'assertMiddleware' ], $_middlewares);

        $pipeline = $this->factory->newPipeline($this->processor);

        foreach ( $_middlewares as $middleware ) {
            $_middleware = $this->parser->assertMiddleware($middleware);

            $pipeline->addMiddleware($_middleware);
        }

        $_middleware = $this->parser->assertMiddleware($_handlers
            ? $this->middlewareEvent($_handlers)
            : $this->middlewareNullEvent()
        );

        $pipeline->addMiddleware($_middleware);

        return $pipeline;
    }

    /**
     * @template-covariant T of callable|EventHandlerInterface|GenericHandler
     * @template-covariant TT of callable|MiddlewareInterface|GenericMiddleware
     *
     * @param T|T[]        $handlers
     * @param TT|TT[]|null $middlewares
     *
     * @return Pipeline
     */
    public function pipelineFilter($handlers, $middlewares = null) : Pipeline
    {
        $middlewares = $middlewares ?? [];

        $_handlers = _list($handlers, '\Gzhegow\Eventman\_filter_method');
        $_middlewares = _list($middlewares, '\Gzhegow\Eventman\_filter_method');

        $_handlers = array_map([ $this->parser, 'assertHandler' ], $_handlers);
        $_middlewares = array_map([ $this->parser, 'assertMiddleware' ], $_middlewares);

        $pipeline = $this->factory->newPipeline($this->processor);

        foreach ( $_middlewares as $middleware ) {
            $_middleware = $this->parser->assertMiddleware($middleware);

            $pipeline->addMiddleware($_middleware);
        }

        $_middleware = $this->parser->assertMiddleware($_handlers
            ? $this->middlewareFilter($_handlers)
            : $this->middlewareNullFilter()
        );

        $pipeline->addMiddleware($_middleware);

        return $pipeline;
    }


    /**
     * @param string|GenericPoint                           $point
     * @param callable|EventHandlerInterface|GenericHandler $handler
     *
     * @return void
     */
    public function onEvent($point, $handler) : void
    {
        $_point = $this->parser->assertPoint($point);
        $_handler = $this->parser->assertHandler($handler);

        $pointsIndex = $this->parser->parseEventPoints($_point);

        $this->queue[] = $_handler;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ static::TASK_TYPE_EVENT ][ $idx ] = true;

        foreach ( $pointsIndex as $p => $bool ) {
            $this->taggedQueue[ $p ][ $idx ] = true;
        }
    }

    /**
     * @param string|GenericPoint                            $point
     * @param callable|FilterHandlerInterface|GenericHandler $handler
     *
     * @return void
     */
    public function onFilter($point, $handler) : void
    {
        $_point = $this->parser->assertPoint($point);
        $_handler = $this->parser->assertHandler($handler);

        $pointsIndex = $this->parser->parseEventPoints($_point);

        $this->queue[] = $_handler;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ static::TASK_TYPE_FILTER ][ $idx ] = true;

        foreach ( $pointsIndex as $p => $bool ) {
            $this->taggedQueue[ $p ][ $idx ] = true;
        }
    }


    /**
     * @param string|GenericPoint                            $point
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     *
     * @return void
     */
    public function middleEvent($point, $middleware) : void
    {
        $_point = $this->parser->assertPoint($point);
        $_middleware = $this->parser->assertMiddleware($middleware);

        $pointsIndex = $this->parser->parseEventPoints($_point);

        $this->queue[] = $_middleware;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ static::TASK_TYPE_EVENT ][ $idx ] = true;

        foreach ( $pointsIndex as $p => $bool ) {
            $this->taggedQueue[ $p ][ $idx ] = true;
        }
    }

    /**
     * @param string|GenericPoint                            $point
     * @param callable|MiddlewareInterface|GenericMiddleware $middleware
     *
     * @return void
     */
    public function middleFilter($point, $middleware) : void
    {
        $_point = $this->parser->assertPoint($point);
        $_middleware = $this->parser->assertMiddleware($middleware);

        $pointsIndex = $this->parser->parseEventPoints($_point);

        $this->queue[] = $_middleware;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ static::TASK_TYPE_FILTER ][ $idx ] = true;

        foreach ( $pointsIndex as $p => $bool ) {
            $this->taggedQueue[ $p ][ $idx ] = true;
        }
    }


    /**
     * @param class-string<SubscriberInterface>|SubscriberInterface $subscriber
     *
     * @return void
     */
    public function subscribe($subscriber) : void
    {
        $_subscriber = $this->parser->assertSubscriber($subscriber);

        $points = $_subscriber->getPoints();

        $pointsIndex = [];
        foreach ( $points as $i => $point ) {
            $_point = is_int($i)
                ? $point
                : $i;

            $_point = _get(_filter_strlen($_point));

            $pointsIndex[ $_point ] = true;
        }

        $this->queue[] = $_subscriber;

        $idx = _array_key_last($this->queue);

        $this->taggedQueue[ static::TASK_TYPE_EVENT ][ $idx ] = true;
        $this->taggedQueue[ static::TASK_TYPE_FILTER ][ $idx ] = true;

        foreach ( $pointsIndex as $p => $bool ) {
            $this->taggedQueue[ $p ][ $idx ] = true;
        }
    }


    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return void
     */
    public function fireEvent($point, $input = null, $context = null) : void
    {
        [ $middlewares, $handlers ] = $this->task(static::TASK_TYPE_EVENT, $point);

        if ($middlewares) {
            $pipeline = $this->pipelineEvent($handlers, $middlewares);

            $pipeline->run($point, $input, $context);

            return;
        }

        if ($handlers) {
            $fn = $this->callableEvent($handlers);

            $this->processor->callUserFuncArray($fn, [ $point, $input, $context ]);
        }
    }

    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return mixed
     */
    public function applyFilter($point, $input = null, $context = null) // : mixed
    {
        [ $middlewares, $handlers ] = $this->task(static::TASK_TYPE_FILTER, $point);

        $current = $input;

        if ($middlewares) {
            $pipeline = $this->pipelineFilter($handlers, $middlewares);

            $current = $pipeline->run($point, $current, $context);

            return $current;
        }

        if ($handlers) {
            $fn = $this->callableFilter($handlers);

            $current = $this->processor->callUserFuncArray($fn, [ $point, $current, $context ]);
        }

        return $current;
    }


    /**
     * @param string                    $taskType
     * @param string|GenericPoint|array $points
     *
     * @return array{0: GenericMiddleware[], 1: GenericHandler[]}
     */
    public function task(string $taskType, $points) : array
    {
        $_points = _list($points);

        $pointsIndex = [];
        foreach ( $_points as $point ) {
            $_point = $this->parser->assertPoint($point);

            $pointsIndex += $this->parser->parseEventPoints($_point);
        }

        $intersect = [];
        foreach ( $pointsIndex as $p => $bool ) {
            $intersect[] = $this->taggedQueue[ $p ] ?? [];
        }
        $intersect[] = $this->taggedQueue[ $taskType ] ?? [];

        $index = array_intersect_key(...$intersect);

        $middlewares = [];
        $handlers = [];
        foreach ( $index as $i => $bool ) {
            $subscription = $this->queue[ $i ];

            if ($subscription instanceof GenericMiddleware) {
                $middlewares[] = $subscription;

            } elseif ($subscription instanceof GenericHandler) {
                $handlers[] = $subscription;

            } elseif ($subscription instanceof GenericSubscriber) {
                if (! isset($this->subscriberInstances[ $i ])) {
                    $subscriber = $this->factory->newSubscriber($subscription);

                    $this->subscriberInstances[ $i ] = $subscriber;
                }

                $subscriberInstance = $this->subscriberInstances[ $i ];

                if ($subscriberInstance instanceof MiddlewareSubscriberInterface) {
                    foreach ( $subscriberInstance->middlewares() as [ $sEventPoint, $sMiddleware ] ) {
                        if (! isset($pointsIndex[ $sEventPoint ])) {
                            continue;
                        }

                        $_sMiddleware = $this->parser->assertMiddleware($sMiddleware);

                        $middlewares[] = $_sMiddleware;
                    }
                }

                $mapEventType = [
                    static::TASK_TYPE_EVENT  => [ EventSubscriberInterface::class, 'events' ],
                    static::TASK_TYPE_FILTER => [ FilterSubscriberInterface::class, 'filters' ],
                ];

                [ $interface, $objectMethodName ] = $mapEventType[ $taskType ];

                if ($subscriberInstance instanceof $interface) {
                    foreach ( $subscriberInstance->{$objectMethodName}() as [ $sEventPoint, $sHandler ] ) {
                        if (! isset($pointsIndex[ $sEventPoint ])) {
                            continue;
                        }

                        $_sHandler = $this->parser->assertHandler($sHandler);

                        $handlers[] = $_sHandler;
                    }
                }
            }
        }

        return [ $middlewares, $handlers ];
    }


    protected function middlewareNullEvent() : callable
    {
        return static function (Pipeline $pipeline, $point, $input = null, $context = null) {
            return;
        };
    }

    protected function middlewareNullFilter() : callable
    {
        return static function (Pipeline $pipeline, $point, $input = null, $context = null) {
            return $input;
        };
    }


    protected function middlewareEvent(array $handlers) : callable
    {
        $processor = $this->processor;

        return static function (
            Pipeline $pipeline, $point, $input = null, $context = null
        ) use (
            $handlers, $processor
        ) {
            foreach ( $handlers as $handler ) {
                $processor->callHandler($handler, [ $point, $input, $context ]);
            }
        };
    }

    protected function callableEvent(array $handlers) : callable
    {
        $processor = $this->processor;

        return static function (
            $point, $input = null, $context = null
        ) use (
            $handlers, $processor
        ) {
            foreach ( $handlers as $handler ) {
                $processor->callHandler($handler, [ $point, $input, $context ]);
            }
        };
    }


    protected function middlewareFilter(array $handlers) : callable
    {
        $processor = $this->processor;

        return static function (
            Pipeline $pipeline, $point, $input = null, $context = null
        ) use (
            $handlers, $processor
        ) {
            $current = $input;

            foreach ( $handlers as $handler ) {
                $current = $processor->callHandler($handler, [ $point, $current, $context ]);
            }

            return $current;
        };
    }

    protected function callableFilter(array $handlers) : callable
    {
        $processor = $this->processor;

        return static function (
            $point, $input = null, $context = null
        ) use (
            $handlers, $processor
        ) {
            $current = $input;

            foreach ( $handlers as $handler ) {
                $current = $processor->callHandler($handler, [ $point, $current, $context ]);
            }

            return $current;
        };
    }
}
