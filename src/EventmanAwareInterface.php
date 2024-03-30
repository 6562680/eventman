<?php

namespace Gzhegow\Eventman;


interface EventmanAwareInterface
{
    /**
     * @param null|EventmanInterface $eventman
     *
     * @return void
     */
    public function setEventman(?EventmanInterface $eventman) : void;
}
