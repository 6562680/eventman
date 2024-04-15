<?php

namespace Gzhegow\Eventman\Subscriber;


interface SubscriberInterface
{
    /**
     * @return array<string, bool>
     */
    public static function points() : array;
}
