<?php

namespace Gzhegow\Eventman\Pipeline;

use Gzhegow\Eventman\EventmanProcessor;
use Gzhegow\Eventman\Struct\GenericPoint;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\EventmanProcessorInterface;


class Pipeline
{
    /**
     * @var EventmanProcessor
     */
    protected $processor;

    /**
     * @var array<int, GenericMiddleware>
     */
    protected $middlewares = [];


    /**
     * @param EventmanProcessorInterface $processor
     */
    public function __construct(
        EventmanProcessorInterface $processor
    )
    {
        $this->processor = $processor;
    }


    public function addMiddleware(GenericMiddleware $middleware) : void
    {
        $this->middlewares[] = $middleware;
    }


    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return mixed
     */
    public function run($point, $input = null, $context = null)
    {
        reset($this->middlewares);

        $result = $this->processor->callMiddleware(
            current($this->middlewares),
            [ $this, $point, $input, $context ]
        );

        return $result;
    }

    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return mixed
     */
    public function next($point, $input = null, $context = null)
    {
        next($this->middlewares);

        $result = $this->processor->callMiddleware(
            current($this->middlewares),
            [ $this, $point, $input, $context ]
        );

        return $result;
    }
}
