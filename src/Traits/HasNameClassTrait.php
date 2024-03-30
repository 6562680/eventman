<?php

namespace Gzhegow\Eventman\Traits;

trait HasNameClassTrait
{
    public function getName() : string
    {
        return get_class($this);
    }
}
