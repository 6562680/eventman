<?php

namespace Gzhegow\Eventman;


trait EventmanAwareTrait
{
    /**
     * @var Eventman
     */
    protected $eventman;


    /**
     * @param null|EventmanInterface $eventman
     *
     * @return void
     */
    public function setEventman(?EventmanInterface $eventman) : void
    {
        $this->eventman = $eventman;
    }
}
