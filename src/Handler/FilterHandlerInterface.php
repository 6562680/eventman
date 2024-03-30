<?php

namespace Gzhegow\Eventman\Handler;


interface FilterHandlerInterface
{
    /**
     * @return mixed
     */
    public function handle($filter, $input, $context = null);
}
