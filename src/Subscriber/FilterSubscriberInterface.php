<?php

namespace Gzhegow\Eventman\Subscriber;

use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Handler\FilterHandlerInterface;


interface FilterSubscriberInterface extends SubscriberInterface
{
    /**
     * @return array{0: string, 1: callable|FilterHandlerInterface|GenericHandler}[]
     */
    public function filters() : array;
}
