<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


class EventmanFactory implements EventmanFactoryInterface
{
    public function newParser() : EventmanParserInterface
    {
        return new EventmanParser();
    }

    public function newProcessor() : EventmanProcessorInterface
    {
        return new EventmanProcessor($this);
    }


    public function newHandlerCallable(GenericHandler $handler) // : callable
    {
        $callable = null
            ?? $this->newHandlerCallableByCallable($handler)
            ?? $this->newHandlerCallableByEventHandler($handler)
            ?? $this->newHandlerCallableByFilterHandler($handler)
            ?? $this->newHandlerCallableByHandler($handler)
            ?? $this->newHandlerCallableByInvokableClass($handler)
            ?? $this->newHandlerCallableByPublicMethod($handler);

        _get($callable);

        return $callable;
    }

    protected function newHandlerCallableByCallable(GenericHandler $handler) // : ?callable
    {
        if (! $handler->callable) return null;

        return $handler->callable;
    }

    protected function newHandlerCallableByEventHandler(GenericHandler $handler) // : ?callable
    {
        if ($handler->eventHandler) {
            return [ $handler->eventHandler, 'handle' ];
        }

        if ($handler->eventHandlerClass) {
            $class = $handler->eventHandlerClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    protected function newHandlerCallableByFilterHandler(GenericHandler $handler) // : ?callable
    {
        if ($handler->filterHandler) {
            return [ $handler->filterHandler, 'handle' ];
        }

        if ($handler->filterHandlerClass) {
            $class = $handler->filterHandlerClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    protected function newHandlerCallableByHandler(GenericHandler $handler) // : ?callable
    {
        if ($handler->handler) {
            return [ $handler->handler, 'handle' ];
        }

        if ($handler->handlerClass) {
            $class = $handler->handlerClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    protected function newHandlerCallableByInvokableClass(GenericHandler $handler) // : ?callable
    {
        if (! $handler->invokableClass) return null;

        $class = $handler->invokableClass;

        return new $class();
    }

    protected function newHandlerCallableByPublicMethod(GenericHandler $handler) // : ?callable
    {
        if (! $handler->publicMethod) return null;

        [ $class, $method ] = $handler->publicMethod;

        return [ new $class(), $method ];
    }


    public function newMiddlewareCallable(GenericMiddleware $middleware) // : callable
    {
        $callable = null
            ?? $this->newMiddlewareCallableByCallable($middleware)
            ?? $this->newMiddlewareCallableByMiddleware($middleware)
            ?? $this->newMiddlewareCallableByInvokableClass($middleware)
            ?? $this->newMiddlewareCallableByPublicMethod($middleware);

        _get($callable);

        return $callable;
    }

    protected function newMiddlewareCallableByCallable(GenericMiddleware $middleware) // : ?callable
    {
        if (! $middleware->callable) return null;

        return $middleware->callable;
    }

    protected function newMiddlewareCallableByMiddleware(GenericMiddleware $middleware) // : ?callable
    {
        if ($middleware->middleware) {
            return [ $middleware->middleware, 'handle' ];
        }

        if ($middleware->middlewareClass) {
            $class = $middleware->middlewareClass;

            return [ new $class(), 'handle' ];
        }

        return null;
    }

    protected function newMiddlewareCallableByInvokableClass(GenericMiddleware $middleware) // : ?callable
    {
        if (! $middleware->invokableClass) return null;

        $class = $middleware->invokableClass;

        return new $class();
    }

    protected function newMiddlewareCallableByPublicMethod(GenericMiddleware $middleware) // : ?callable
    {
        if (! $middleware->publicMethod) return null;

        [ $class, $method ] = $middleware->publicMethod;

        return [ new $class(), $method ];
    }


    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface
    {
        if ($subscriber->subscriber) {
            return $subscriber->subscriber;
        }

        if ($subscriber->subscriberClass) {
            $class = $subscriber->subscriberClass;

            return new $class();
        }

        throw new \RuntimeException(
            'Unable to create `subscriber` object from: '
            . _php_dump($subscriber)
        );
    }


    public function newPipeline(EventmanProcessorInterface $processor) : Pipeline
    {
        $pipeline = new Pipeline($processor);

        return $pipeline;
    }
}
