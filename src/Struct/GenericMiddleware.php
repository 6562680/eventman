<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Handler\MiddlewareInterface;
use function Gzhegow\Eventman\_php_dump;
use function Gzhegow\Eventman\_filter_method;


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


    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function from($middleware, $context = null)
    {
        if (is_a($middleware, GenericMiddleware::class)) {
            return $middleware;
        }

        $_middleware = null;
        $_middlewareClass = null;
        $_callable = null;
        $_invokableClass = null;
        $_publicMethod = null;
        if (is_object($middleware)) {
            if ($middleware instanceof MiddlewareInterface) {
                $_middleware = $middleware;
                $_middlewareClass = get_class($middleware);

            } elseif (is_callable($middleware)) {
                $_callable = $middleware;
            }

        } elseif (is_array($middleware)) {
            if (is_callable($middleware)) {
                $_callable = $middleware;

            } elseif (($_middleware = _filter_method($middleware, [ true ], $rm)) && $rm->isPublic()) {
                $_publicMethod = $_middleware;
            }

        } else {
            if (is_string($middleware) && ('' !== $middleware)) {
                if (class_exists($middleware)) {
                    if (is_subclass_of($middleware, MiddlewareInterface::class)) {
                        $_middlewareClass = $middleware;

                    } elseif (method_exists($middleware, '__invoke')) {
                        $_invokableClass = $middleware;
                    }

                } elseif (is_callable($middleware)) {
                    $_callable = $middleware;
                }
            }
        }

        $parsed = null
            ?? $_middleware
            ?? $_middlewareClass
            ?? $_callable
            ?? $_invokableClass
            ?? $_publicMethod;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _php_dump($middleware)
            );
        }

        $generic = new GenericMiddleware();
        $generic->middleware = $_middleware;
        $generic->middlewareClass = $_middlewareClass;
        $generic->callable = $_callable;
        $generic->invokableClass = $_invokableClass;
        $generic->publicMethod = $_publicMethod;
        $generic->context = $context;

        return $generic;
    }


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
