<?php

namespace Gzhegow\Eventman\Handler;


class DemoFilterHandler implements FilterHandlerInterface
{
    public function handle($point, $input = null, $context = null)
    {
        echo __METHOD__ . PHP_EOL;

        $result = $input;

        return $result;
    }
}
