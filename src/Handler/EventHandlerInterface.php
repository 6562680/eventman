<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Struct\GenericPoint;


interface EventHandlerInterface extends HandlerInterface
{
    /**
     * @param string|GenericPoint $point
     * @param mixed|null          $input
     * @param mixed|null          $context
     *
     * @return void
     */
    public function handle($point, $input = null, $context = null) : void;
}
