<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

use Pagerfanta\PagerfantaInterface;

class PostsCollection
{
    private $pager;

    public function __construct(PagerfantaInterface $pager)
    {
        $this->pager = $pager;
    }
}
