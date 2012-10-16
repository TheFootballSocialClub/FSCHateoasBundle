<?php

namespace FSC\HateoasBundle\Factory;

use Pagerfanta\PagerfantaInterface;
use FSC\HateoasBundle\Factory\ParametersFactoryInterface;

use FSC\HateoasBundle\Model\RouteAwarePager;

class RouteAwarePagerFactory implements RouteAwarePagerFactoryInterface
{
    protected $parametersFactory;

    public function __construct(ParametersFactoryInterface $parametersFactory)
    {
        $this->parametersFactory = $parametersFactory;
    }

    public function create(PagerfantaInterface $pager, $relationMeta, $object)
    {
        $parameters = $this->parametersFactory->createParameters($object, $relationMeta['params']);

        return new RouteAwarePager($pager, $relationMeta['route'], $parameters);
    }
}
