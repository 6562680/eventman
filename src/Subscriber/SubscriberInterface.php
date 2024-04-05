<?php

namespace Gzhegow\Eventman\Subscriber;


interface SubscriberInterface
{
    /**
     * @return array{0: string, 1: callable}[]
     */
    public function eventHandlers() : array;

    /**
     * @return array{0: string, 1: callable}[]
     */
    public function filterHandlers() : array;


    /**
     * @return array<string, bool>
     */
    public static function events() : array;

    /**
     * @return array<string, bool>
     */
    public static function filters() : array;
}
