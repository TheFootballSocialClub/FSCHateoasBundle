<?php

namespace FSC\HateoasBundle\Factory;

use Pagerfanta\PagerfantaInterface;

interface RouteAwarePagerFactoryInterface
{
    public function create(PagerfantaInterface $pager, $relationMeta, $object);
}
