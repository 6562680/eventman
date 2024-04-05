<?php

namespace Gzhegow\Eventman\Struct;

use Gzhegow\Eventman\Filter\FilterInterface;


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
     * @var object
     */
    public $filterObject;
    /**
     * @var class-string
     */
    public $filterObjectClass;

    /**
     * @var class-string
     */
    public $filterClassString;

    /**
     * @var string
     */
    public $filterString;

    /**
     * @var mixed
     */
    public $context;


    public function getFilter() : FilterInterface
    {
        return $this->filter;
    }

    /**
     * @return class-string<FilterInterface>|FilterInterface
     */
    public function getFilterClass() : string
    {
        return $this->filterClass;
    }


    public function getFilterObject() : object
    {
        return $this->filterObject;
    }

    /**
     * @return class-string
     */
    public function getFilterObjectClass() : string
    {
        return $this->filterObjectClass;
    }


    /**
     * @return class-string
     */
    public function getFilterClassString() : string
    {
        return $this->filterClassString;
    }


    public function getFilterString() : string
    {
        return $this->filterString;
    }


    public function getContext()
    {
        return $this->context;
    }
}
