<?php

namespace Gzhegow\Eventman;


/**
 * > gzhegow, конвертирует NULL в исключение
 * > $value = _get($arr['hello'] ?? null, 'Значение arr[hello] не должно быть null');
 */
function _get($value, $error = '') // : mixed
{
    if (null === $value) {
        $_error = null
            ?? ($error ?: null)
            ?? (('0' === $error) ? $error : null)
            ?? 'The `value` should be not null';

        throw _php_throw($_error);
    }

    return $value;
}


/**
 * > gzhegow, вызывает произвольный колбэк с аргументами, не пропускает null
 * > бросает исключение, позволяет указать ошибку для исключения
 * > _assert_value('_filter_int', $input, 'Переменная `input` должна быть числом') ?? 1;
 *
 * @param callable   $fn
 * @param mixed      $value
 * @param mixed|null $error
 * @param array|null $fnArgs
 *
 * @return mixed|null
 */
function _assert_value($fn, $value, $error = '', array $fnArgs = null) // : mixed
{
    if (null === $value) {
        return null;
    }

    $_error = null
        ?? ($error ?: null)
        ?? (('0' === $error) ? $error : null)
        ?? (is_string($fn) ? "[ ASSERT ] {$fn}" : null);

    if (null === $value) {
        throw _php_throw($_error);
    }

    $_args = $fnArgs ?? [];

    array_unshift($_args, $value);

    $result = call_user_func_array($fn, $_args);

    if (null === $result) {
        throw _php_throw($_error);
    }

    return $result;
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

function _filter_positive_int($value) : ?int
{
    if (null === ($_value = _filter_int($value))) {
        return null;
    }

    if ($_value <= 0) {
        return null;
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

function _filter_strlen($value, array $optional = [], array &$maxmin = null) : ?string
{
    $optional[ 0 ] = $optional[ 0 ] ?? null;
    $optional[ 1 ] = $optional[ 1 ] ?? 1;

    $maxmin[ 0 ] = null; // &$max
    $maxmin[ 1 ] = null; // &$min

    if (null === ($_value = _filter_string($value))) {
        return null;
    }

    $_value = trim($_value);

    $maxmin[ 0 ] = _filter_positive_int($optional[ 0 ]);
    $maxmin[ 1 ] = _filter_positive_int($optional[ 1 ]);

    $isMax = isset($maxmin[ 0 ]);
    $isMin = isset($maxmin[ 1 ]);

    if ($isMax || $isMin) {
        $len = strlen($_value);

        if ($isMax && $isMin) {
            if ($maxmin[ 0 ] < $maxmin[ 1 ]) {
                throw _php_throw(
                    'The `max` should be >= `min`'
                );
            }
        }

        if (isset($maxmin[ 0 ]) && ($len > $maxmin[ 0 ])) {
            return null;
        }

        if (isset($maxmin[ 1 ]) && ($len < $maxmin[ 1 ])) {
            return null;
        }

    } else {
        if ('' === $_value) {
            return null;
        }
    }

    return $_value;
}


function _filter_method($method, array $optional = [], \ReflectionMethod &$rm = null) : ?array
{
    $optional[ 0 ] = $optional[ 0 ] ?? false;

    $useReflection = (bool) $optional[ 0 ];

    $rm = null;

    $_class = null;
    $_object = null;
    $_objectOrClass = null;
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

        $_objectOrClass = $_class;
    }

    if (! $_objectOrClass) {
        return null;
    }

    $_methodArray = [ $_objectOrClass, $_method ];

    if ($_object && is_callable($_methodArray)) {
        return $_method;
    }

    if ($useReflection) {
        try {
            $rm = new \ReflectionMethod($_class, $_method);
        }
        catch ( \ReflectionException $e ) {
            return null;
        }

    } else {
        if ($_class && ! class_exists($_class)) {
            return null;
        }

        $_objectOrClass = $_object ?? $_class;

        if (! $_objectOrClass) {
            return null;
        }

        if (! $_method || ! method_exists($_class, $_method)) {
            return null;
        }
    }

    return $_methodArray;
}
