<?php

namespace Gzhegow\Eventman;


function _array_key_first(array $src) // : ?int|string
{
    reset($src);

    return key($src);
}

function _array_key_last(array $src) // : ?int|string
{
    end($src);

    return key($src);
}
