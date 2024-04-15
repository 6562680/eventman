<?php

namespace Gzhegow\Eventman\Struct;

use function Gzhegow\Eventman\_php_dump;
use function Gzhegow\Eventman\_filter_strlen;


class GenericPoint
{
    /**
     * @var object
     */
    public $pointObject;
    /**
     * @var class-string
     */
    public $pointObjectClass;

    /**
     * @var class-string
     */
    public $pointClassString;

    /**
     * @var string
     */
    public $pointString;

    /**
     * @var mixed
     */
    public $context;


    private function __construct()
    {
    }

    /**
     * @return static
     */
    public static function from($point, $context = null)
    {
        if (is_a($point, GenericPoint::class)) {
            return $point;
        }

        $_pointObject = null;
        $_pointObjectClass = null;
        $_pointClassString = null;
        $_pointString = null;
        if (is_object($point)) {
            $class = get_class($point);

            $_pointObject = $point;
            $_pointObjectClass = $class;

        } elseif (null !== ($_point = _filter_strlen($point))) {
            if (class_exists($_point)) {
                $_pointClassString = $_point;

            } else {
                $_pointString = $_point;
            }
        }

        $parsed = null
            ?? $_pointObject
            ?? $_pointClassString
            ?? $_pointString;

        if ((null === $parsed)) {
            throw new \LogicException(
                'Unable to ' . __METHOD__ . ': '
                . _php_dump($point)
            );
        }

        $generic = new GenericPoint();
        $generic->pointObject = $_pointObject;
        $generic->pointObjectClass = $_pointObjectClass;
        $generic->pointClassString = $_pointClassString;
        $generic->pointString = $_pointString;
        $generic->context = $context;

        return $generic;
    }


    public function getPointObject() : object
    {
        return $this->pointObject;
    }

    /**
     * @return class-string
     */
    public function getPointObjectClass() : string
    {
        return $this->pointObjectClass;
    }


    /**
     * @return class-string
     */
    public function getPointClassString() : string
    {
        return $this->pointClassString;
    }


    public function getPointString() : string
    {
        return $this->pointString;
    }


    public function getContext()
    {
        return $this->context;
    }
}
