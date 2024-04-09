<?php

namespace Gzhegow\Eventman;


function _array_key_last(array $src) // : ?int|string
{
    end($src);

    return key($src);
}
