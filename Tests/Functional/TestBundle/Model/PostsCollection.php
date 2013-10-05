<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

class PostsCollection
{
    private $pager;
    private $user;

    public function __construct($pager, $user)
    {
        $this->pager = $pager;
        $this->user = $user;
    }
}
