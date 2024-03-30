<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Filter\FilterInterface;
use Gzhegow\Eventman\Interfaces\HasNameInterface;


class GenericFilter
{
    /**
     * @var FilterInterface
     */
    public $filter;
    /**
     * @var class-string<FilterInterface>|FilterInterface
     */
    public $filterClass;
    /**
     * @var string
     */
    public $filterString;


    public function getName() : string
    {
        if ($this->filterClass) {
            return $this->filterClass;
        }

        if ($this->filterString) {
            return $this->filterString;
        }

        if ($this->filter) {
            if ($this->filter instanceof HasNameInterface) {
                return $this->filter->getName();
            }

            return get_class($this->filter);
        }

        throw new \RuntimeException('Unable to extract `name` from filter');
    }


    public function isSame(GenericFilter $filter) : bool
    {
        return ($this->filterString && ($this->filterString === $filter->filterString))
            || ($this->filter && ($this->filter === $filter->filter))
            || ($this->filterClass && ($this->filterClass === $filter->filterClass));
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

    public function getFilterString() : string
    {
        return $this->filterString;
    }
}
