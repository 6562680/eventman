<?php

namespace Gzhegow\Eventman\Handler;


class DemoFilterHandler implements FilterHandlerInterface
{
    public function handle($filter, $input, $context = null)
    {
        echo __CLASS__ . PHP_EOL;

        $result = $input;

        return $result;
    }
}
