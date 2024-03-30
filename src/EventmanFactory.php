<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericSubscriber;
use Gzhegow\Eventman\Subscriber\SubscriberInterface;


class EventmanFactory implements EventmanFactoryInterface
{
    public function newHandler(GenericHandler $handler) : callable
    {
        return null
            ?? $this->newHandlerCallable($handler)
            ?? $this->newHandlerEventHandler($handler)
            ?? $this->newHandlerFilterHandler($handler)
            ?? $this->newHandlerInvokableClass($handler);
    }

    protected function newHandlerCallable(GenericHandler $handler) : ?callable
    {
        if (! $handler->callable) return null;

        return $handler->getCallable();
    }

    protected function newHandlerEventHandler(GenericHandler $handler) : ?callable
    {
        if ($handler->eventHandler) {
            return [ $handler->eventHandler, 'handle' ];
        }

        if ($handler->eventHandlerClass) {
            return [ new $handler->eventHandlerClass(), 'handle' ];
        }

        return null;
    }

    protected function newHandlerFilterHandler(GenericHandler $handler) : ?callable
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

    protected function newHandlerInvokableClass(GenericHandler $handler) : ?callable
    {
        if (! $handler->invokableClass) return null;

        $class = $handler->getInvokableClass();

        return new $class();
    }


    public function newSubscriber(GenericSubscriber $subscriber) : SubscriberInterface
    {
        $class = $subscriber->getSubscriberClass();

        return new $class();
    }


    public function callHandler(GenericHandler $handler, array $arguments = null)
    {
        $arguments = $arguments ?? [];

        $callable = $this->newHandler($handler);

        $result = call_user_func_array($callable, $arguments);

        return $result;
    }
}
