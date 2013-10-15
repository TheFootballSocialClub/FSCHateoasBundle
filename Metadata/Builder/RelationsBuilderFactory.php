<?php

namespace FSC\HateoasBundle\Metadata\Builder;

class RelationsBuilderFactory
{
    protected $defaultPageParameterName;
    protected $defaultLimitParameterName;

    public function __construct(
        $defaultPageParameterName = 'page',
        $defaultLimitParameterName = 'limit'
    ) {

        $this->defaultPageParameterName = $defaultPageParameterName;
        $this->defaultLimitParameterName = $defaultLimitParameterName;
    }

    public function create()
    {
        return new RelationsBuilder($this->defaultPageParameterName, $this->defaultLimitParameterName);
    }
}
