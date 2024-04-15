<?php

namespace Gzhegow\Eventman;


/**
 * @param mixed    $value
 * @param callable $fn
 * @param array    $fnArgs
 *
 * @return array|null
 */
function _list($value, $fn = null, array $fnArgs = []) : ?array
{
    /**
     * > gzhegow, к сожалению, `(array) $object` выполнит преобразование, а не обернет в массив, иногда так не надо
     * > также ассоциативные массивы в списки напрямую превращать нельзя
     * > с другой стороны, `(array) $fn` или `(array) $resource` обернет в массив, и это правильно и хорошо
     */

    if (null === $value) {
        return null;
    }

    if (! is_array($value)) {
        return [ $value ];
    }

    if ($fn) {
        $_fnArgs = $fnArgs;

        array_unshift($_fnArgs, $value);

        $result = call_user_func_array($fn, $_fnArgs);

        if (null !== $result) {
            return [ $value ];
        }
    }

    // > gzhegow, associative array is not a list
    foreach ( array_keys($value) as $key ) {
        if (! is_int($key)) {
            return [ $value ];
        }
    }

    return $value;
}
