<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Handler\MiddlewareInterface;
use Gzhegow\Eventman\Handler\EventHandlerInterface;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


class GenericMiddleware
{
    /**
     * @var MiddlewareInterface
     */
    public $middleware;
    /**
     * @var class-string<MiddlewareInterface>|MiddlewareInterface
     */
    public $middlewareClass;

    /**
     * @var callable
     */
    public $callable;

    /**
     * @var class-string
     */
    public $invokableClass;

    /**
     * @var array|callable
     */
    public $publicMethod;

    /**
     * @var mixed
     */
    public $context;


    public function getMiddleware() : MiddlewareInterface
    {
        return $this->middleware;
    }

    /**
     * @return class-string<MiddlewareInterface>|MiddlewareInterface
     */
    public function getMiddlewareClass() : string
    {
        return $this->middlewareClass;
    }


    public function getCallable() : callable
    {
        return $this->callable;
    }


    /**
     * @return class-string
     */
    public function getInvokableClass() : string
    {
        return $this->invokableClass;
    }


    /**
     * @return array|callable
     */
    public function getPublicMethod() : array
    {
        return $this->publicMethod;
    }


    public function getContext()
    {
        return $this->context;
    }
}
