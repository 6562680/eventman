<?php

namespace Gzhegow\Eventman\Handler;


class DemoEventHandler implements EventHandlerInterface
{
    public function handle($event, $input = null, $context = null) : void
    {
        echo __METHOD__ . PHP_EOL;
    }
}
