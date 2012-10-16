<?php

namespace FSC\HateoasBundle\Factory;

use Pagerfanta\PagerfantaInterface;

use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

interface RouteAwarePagerFactoryInterface
{
    public function create(PagerfantaInterface $pager, RelationMetadataInterface $relationMetadata, $object);
}
