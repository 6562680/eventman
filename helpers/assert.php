<?php

namespace Gzhegow\Eventman;


function _assert_dump($value) : string
{
    if (! is_iterable($value)) {
        $_value = null
            ?? (($value === null) ? '{ NULL }' : null)
            ?? (($value === false) ? '{ FALSE }' : null)
            ?? (($value === true) ? '{ TRUE }' : null)
            ?? (is_object($value) ? ('{ object(' . get_class($value) . ' # ' . spl_object_id($value) . ') }') : null)
            ?? (is_resource($value) ? ('{ resource(' . gettype($value) . ' # ' . ((int) $value) . ') }') : null)
            //
            ?? (is_int($value) ? (var_export($value, 1)) : null) // INF
            ?? (is_float($value) ? (var_export($value, 1)) : null) // NAN
            ?? (is_string($value) ? ('"' . $value . '"') : null)
            //
            ?? null;

    } else {
        foreach ( $value as $k => $v ) {
            $value[ $k ] = null
                ?? (is_array($v) ? '{ array(' . count($v) . ') }' : null)
                ?? (is_iterable($v) ? '{ iterable(' . get_class($value) . ' # ' . spl_object_id($value) . ') }' : null)
                ?? _assert_dump($v);
        }

        $_value = var_export($value, true);

        $_value = str_replace("\n", ' ', $_value);
        $_value = preg_replace('/\s+/', ' ', $_value);
    }

    if (null === $_value) {
        throw _assert_throw(
            [ 'Unable to dump variable', $value ]
        );
    }

    return $_value;
}

/**
 * @param string|array|\LogicException $error
 *
 * @return \LogicException|null
 */
function _assert_throw($error, $code = null, $previous = null) : ?object
{
    if (is_a($error, \LogicException::class)) {
        return $error;
    }

    return new \LogicException($error, $code, $previous);
}


function _filter_int($value) : ?int
{
    if (is_int($value)) {
        return $value;
    }

    if (is_string($value)) {
        if (! is_numeric($value)) {
            return null;
        }
    }

    $valueOriginal = $value;

    if (! is_scalar($valueOriginal)) {
        if (null === ($_valueOriginal = _filter_string($valueOriginal))) {
            return null;
        }

        if (! is_numeric($_valueOriginal)) {
            return null;
        }

        $valueOriginal = $_valueOriginal;
    }

    $_value = $valueOriginal;
    $status = @settype($_value, 'integer');

    if ($status) {
        if ((float) $valueOriginal !== (float) $_value) {
            return null;
        }

        return $_value;
    }

    return null;
}

function _assert_int($value) : ?int
{
    if (null === $value) return null;

    if (null === ($_value = _filter_int($value))) {
        throw _assert_throw(
            [ 'The `value` should be integer', $value ]
        );
    }

    return $_value;
}


function _filter_string($value) : ?string
{
    if (is_string($value)) {
        return $value;
    }

    if (is_array($value)) {
        return null;
    }

    if (! is_object($value)) {
        $_value = $value;
        $status = @settype($_value, 'string');

        if ($status) {
            return $_value;
        }

    } elseif (method_exists($value, '__toString')) {
        $_value = (string) $value;

        return $_value;
    }

    return null;
}

function _assert_string($value) : ?string
{
    if (null === $value) return null;

    if (null === ($_value = _filter_string($value))) {
        throw _assert_throw(
            [ 'The `value` should be string', $value ]
        );
    }

    return $_value;
}


function _filter_strlen($value,
    int $max = null, int $min = null,
    int &$_max = null, int &$_min = null
) : ?string
{
    $min = $min ?? 1;

    $_max = null;
    $_min = null;

    if (null === ($_value = _filter_string($value))) {
        return null;
    }

    $_value = trim($_value);

    [ $_max, $_min ] = (static function () use ($max, $min) {
        $max = _assert_int($max);
        $min = _assert_int($min);

        $isMax = ! is_null($max);
        $isMin = ! is_null($min);

        if ($isMax && ($max <= 0)) {
            $isMax = false;
            $max = null;
        }

        if ($isMin && ($min <= 0)) {
            $isMin = false;
            $min = null;
        }

        if (($isMax && $isMin) && ($max < $min)) {
            throw _assert_throw([
                'The `max` should be greater than or equal to `min`',
                "{$min} > {$max}",
            ]);
        }

        return [ $max, $min ];
    })();

    if (isset($_max) || isset($_min)) {
        $len = strlen($_value);

        if ($_max && ($len > $_max)) {
            return null;
        }

        if ($_min && ($len < $_min)) {
            return null;
        }

    } else {
        if ('' === $_value) {
            return null;
        }
    }

    return $_value;
}

function _assert_strlen($value,
    int $max = null, int $min = null,
    int &$_max = null, int &$_min = null
) : ?string
{
    if (null === $value) return null;

    if (null === ($_value = _filter_strlen($value,
            $max, $min,
            $_max, $_min))
    ) {
        throw _assert_throw(
            [ 'The `value` should be string of given length', $_min, $_max, $value ]
        );
    }

    return $_value;
}


function _filter_word($value,
    int $max = null, int $min = null,
    int &$_max = null, int &$_min = null
) : ?string
{
    $max = $max ?? 1000;
    $min = $min ?? 1;

    if (null === ($_value = _filter_strlen($value,
            $max, $min,
            $_max, $_min
        ))
    ) {
        return null;
    }

    if (false !== strpos($_value, ' ')) {
        return null;
    }

    if (false === preg_match('/\s/', $_value, $m)) {
        return null;
    }

    if ($m) {
        return null;
    }

    return $_value;
}

function _assert_word($value,
    int $max = null, int $min = null,
    int &$_max = null, int &$_min = null
) : ?string
{
    if (null === $value) return null;

    if (null === ($_value = _filter_word($value,
            $max, $min,
            $_max, $_min
        ))
    ) {
        throw _assert_throw(
            [ 'The `value` should be word of given length', $_min, $_max, $value ]
        );
    }

    return $_value;
}


function _filter_method($method) : ?array
{
    $_class = null;
    $_object = null;
    $_method = null;
    if (is_array($method)) {
        [ $_objectOrClass, $_method ] = $method + [ null, null ];

        if (is_object($_objectOrClass)) {
            $_object = $_objectOrClass;

        } elseif (is_string($_objectOrClass) && ('' !== $_objectOrClass)) {
            $_class = $_objectOrClass;
        }

    } elseif (is_string($method) && (false !== strpos('::', $method))) {
        [ $_class, $_method ] = explode('::', $method, 2);
    }

    if ($_class && ! class_exists($_class)) {
        return null;
    }

    $_objectOrClass = $_object ?? $_class;

    if (! $_objectOrClass) {
        return null;
    }

    if (! $_method || ! method_exists($_objectOrClass, $_method)) {
        return null;
    }

    return [ $_objectOrClass, $method ];
}

function _assert_method($value) : ?array
{
    if (null === $value) return null;

    if (null === ($_value = _filter_method($value))) {
        throw _assert_throw(
            [ 'The `value` should be method', $value ]
        );
    }

    return $_value;
}
