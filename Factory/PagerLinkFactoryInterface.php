<?php

namespace FSC\HateoasBundle\Factory;

use Pagerfanta\PagerfantaInterface;

interface PagerLinkFactoryInterface
{
    public function createPagerLinks($object, PagerfantaInterface $pager, $relationData);
}
