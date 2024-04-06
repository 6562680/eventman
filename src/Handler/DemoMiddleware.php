<?php

namespace Gzhegow\Eventman\Handler;

use Gzhegow\Eventman\Pipeline\Pipeline;


class DemoMiddleware implements MiddlewareInterface
{
    public function handle($event, Pipeline $pipeline, $input = null, $context = null)
    {
        echo __METHOD__ . '@before' . PHP_EOL;

        $result = $pipeline->next($event, $input, $context);

        echo __METHOD__ . '@after' . PHP_EOL;

        return $result;
    }
}
