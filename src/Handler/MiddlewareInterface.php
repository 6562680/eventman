<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Pipeline\Pipeline;
use Gzhegow\Eventman\Struct\GenericPoint;


interface MiddlewareInterface
{
    /**
     * @param Pipeline            $pipeline
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return mixed
     */
    public function handle(Pipeline $pipeline, $point, $input = null, $context = null); // : mixed
}
