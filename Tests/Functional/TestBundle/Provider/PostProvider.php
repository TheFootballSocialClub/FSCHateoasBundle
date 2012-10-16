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
            Post::create(2, 'How to create awesome symfony2 application'),
            Post::create(1, 'Welcome on the blog!'),
        );

        $pager = new Pagerfanta(new ArrayAdapter($posts));
        $pager->setMaxPerPage(1);

        return $pager;
    }

    public function getUserLastPost($id)
    {
        return Post::create(2, 'How to create awesome symfony2 application');
    }
}
