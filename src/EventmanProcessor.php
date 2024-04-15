<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericMiddleware;


class EventmanProcessor implements EventmanProcessorInterface
{
    /**
     * @var EventmanFactory
     */
    protected $factory;


    public function __construct(EventmanFactoryInterface $factory)
    {
        $this->factory = $factory;
    }


    public function callMiddleware(GenericMiddleware $middleware, array $arguments = null)
    {
        $callable = $this->factory->newMiddlewareCallable($middleware);

        $result = $this->callUserFuncArray($callable, $arguments);

        return $result;
    }

    public function callHandler(GenericHandler $handler, array $arguments = null)
    {
        $callable = $this->factory->newHandlerCallable($handler);

        $result = $this->callUserFuncArray($callable, $arguments);

        return $result;
    }


    /**
     * @param callable $fn
     */
    public function callUserFuncArray($fn, array $arguments = null)
    {
        $arguments = $arguments ?? [];

        $result = call_user_func_array($fn, $arguments);

        return $result;
    }
}
