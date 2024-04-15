<?php

namespace Gzhegow\Eventman\Handler;


class DemoEventHandler implements EventHandlerInterface
{
    public function handle($point, $input = null, $context = null) : void
    {
        echo __METHOD__ . PHP_EOL;
    }
}
