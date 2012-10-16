<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Provider;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\Post;

class PostProvider
{
    public function getUserPostsPager($id)
    {
        // Ie, in real life there would be a database query
        // That would then return a pager

        $posts = array(
            $this->getPost(2),
            $this->getPost(1),
        );

        $pager = new Pagerfanta(new ArrayAdapter($posts));
        $pager->setMaxPerPage(1);

        return $pager;
    }

    public function getUserLastPost($id)
    {
        return $this->getPost(2);
    }

    public function getPost($id)
    {
        switch ($id) {
            case 1: return Post::create($id, 'Welcome on the blog!');
            case 2: return Post::create(2, 'How to create awesome symfony2 application');
        }
    }
}
