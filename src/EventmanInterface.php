<?php

namespace Gzhegow\Eventman;


interface EventmanInterface
{
    public function onEvent($event, $handler) : void;

    public function onFilter($filter, $handler) : void;

    public function subscribe($subscriber) : void;


    public function fireEvent($event, $context = null) : void;

    public function applyFilter($filter, $input, $context = null);
}
