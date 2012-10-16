<?php

namespace FSC\HateoasBundle\Factory;

use Pagerfanta\PagerfantaInterface;
use FSC\HateoasBundle\Factory\ParametersFactoryInterface;

use FSC\HateoasBundle\Model\RouteAwarePager;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

class RouteAwarePagerFactory implements RouteAwarePagerFactoryInterface
{
    protected $parametersFactory;

    public function __construct(ParametersFactoryInterface $parametersFactory)
    {
        $this->parametersFactory = $parametersFactory;
    }

    public function create(PagerfantaInterface $pager, RelationMetadataInterface $relationMetadata, $object)
    {
        $parameters = $this->parametersFactory->createParameters($object, $relationMetadata->getParams());

        return new RouteAwarePager($pager, $relationMetadata->getRoute(), $parameters);
    }
}
