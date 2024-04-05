<?php

namespace Gzhegow\Eventman\Handler;


interface FilterHandlerInterface
{
    public function handle($filter, $input, $context = null); // : mixed
}
