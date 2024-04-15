<?php

namespace Gzhegow\Eventman;

use Gzhegow\Eventman\Struct\GenericHandler;
use Gzhegow\Eventman\Struct\GenericMiddleware;


interface EventmanProcessorInterface
{
    public function callMiddleware(GenericMiddleware $middleware, array $arguments = null);

    public function callHandler(GenericHandler $handler, array $arguments = null);

    /**
     * @param callable $fn
     */
    public function callUserFuncArray($fn, array $arguments = null);
}
