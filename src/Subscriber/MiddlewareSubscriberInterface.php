<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Struct\GenericMiddleware;
use Gzhegow\Eventman\Handler\MiddlewareInterface;


interface MiddlewareSubscriberInterface extends SubscriberInterface
{
    /**
     * @return array{0: string, 1: callable|MiddlewareInterface|GenericMiddleware}[]
     */
    public function middlewares() : array;
}
