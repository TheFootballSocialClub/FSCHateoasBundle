<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

class PostsCollection
{
    private $pager;

    public function __construct($pager)
    {
        $this->pager = $pager;
    }
}
