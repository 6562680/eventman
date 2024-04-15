<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Struct\GenericPoint;


interface HandlerInterface
{
    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return mixed
     */
    public function handle($point, $input = null, $context = null); // : mixed
}
