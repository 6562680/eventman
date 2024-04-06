<?php

namespace Gzhegow\Eventman\Handler\Internal;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Handler\MiddlewareInterface;


class NullMiddleware implements MiddlewareInterface
{
    public function handle($event, Pipeline $pipeline, $input = null, $context = null)
    {
        return $input;
    }
}
