<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Filter\FilterInterface;


class GenericFilter
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var FilterInterface
     */
    public $filter;
    /**
     * @var class-string<FilterInterface>|FilterInterface
     */
    public $filterClass;


    public function __toString()
    {
        return $this->filterClass
            ?? ($this->filter ? get_class($this->filter) : null)
            ?? $this->name;
    }


    public function isSame(GenericFilter $filter) : bool
    {
        return ($this->name && ($this->name === $filter->name))
            || ($this->filter && ($this->filter === $filter->filter))
            || ($this->filterClass && ($this->filterClass === $filter->filterClass));
    }


    public function getName() : string
    {
        return $this->name;
    }


    public function getFilter() : FilterInterface
    {
        return $this->filter;
    }

    /**
     * @return class-string<FilterInterface>
     */
    public function getFilterClass() : string
    {
        return $this->filterClass;
    }
}
