<?php

namespace Gzhegow\Eventman\Handler;


interface EventHandlerInterface
{
    public function handle($event, $context = null) : void;
}
