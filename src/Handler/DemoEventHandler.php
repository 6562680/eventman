<?php

namespace Gzhegow\Eventman\Handler;


class DemoEventHandler implements EventHandlerInterface
{
    public function handle($event, $context = null) : void
    {
        echo __CLASS__ . PHP_EOL;
    }
}
