<?php

namespace Gzhegow\Eventman\Pipeline;

use Gzhegow\Eventman\Struct\GenericEvent;
use Gzhegow\Eventman\Event\EventInterface;
use Gzhegow\Eventman\EventmanFactoryInterface;
use Gzhegow\Eventman\Struct\GenericMiddleware;


class Pipeline
{
    /**
     * @var EventmanFactoryInterface
     */
    protected $factory;

    /**
     * @var array<int, GenericMiddleware>
     */
    protected $middlewares = [];
    /**
     * @var array<int, callable>
     */
    protected $middlewareCallables = [];


    /**
     * @param EventmanFactoryInterface $factory
     * @param GenericMiddleware[]|null $middlewares
     */
    public function __construct(
        EventmanFactoryInterface $factory,
        array $middlewares = null
    )
    {
        $middlewares = $middlewares ?? [];

        $this->factory = $factory;

        foreach ( $middlewares as $middleware ) {
            $this->addMiddleware($middleware);
        }
    }


    public function addMiddleware(GenericMiddleware $middleware) : void
    {
        $this->middlewares[] = $middleware;
    }


    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $input
     * @param mixed|null                         $context
     *
     * @return mixed
     */
    public function run($event, $input = null, $context = null)
    {
        reset($this->middlewares);

        $key = key($this->middlewares);

        $result = $this->call($key, [ $event, $this, $input, $context ]);

        return $result;
    }

    /**
     * @param string|EventInterface|GenericEvent $event
     * @param mixed|null                         $input
     * @param mixed|null                         $context
     *
     * @return mixed
     */
    public function next($event, $input = null, $context = null)
    {
        next($this->middlewares);

        $key = key($this->middlewares);

        $result = $this->call($key, [ $event, $this, $input, $context ]);

        return $result;
    }


    /**
     * @param int   $middlewaresKey
     * @param array $arguments
     *
     * @return mixed
     */
    protected function call(int $middlewaresKey, array $arguments = [])
    {
        if (! isset($this->middlewareCallables[ $middlewaresKey ])) {
            $middlewareCallable = $this->factory->newMiddlewareCallable(
                $this->middlewares[ $middlewaresKey ]
            );

            $this->middlewareCallables[ $middlewaresKey ] = $middlewareCallable;
        }

        $result = call_user_func_array($this->middlewareCallables[ $middlewaresKey ], $arguments);

        return $result;
    }
}
