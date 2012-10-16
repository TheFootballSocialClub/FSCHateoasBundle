<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Provider;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\Post;

class PostProvider
{
    public function getUserPosts($id)
    {
        // Ie, in real life there would be a database query
        // That would then return a pager

        return array(
            Post::create(2, 'How to create awesome symfony2 application'),
            Post::create(1, 'Welcome on the blog!'),
        );
    }

    public function getUserLastPost($id)
    {
        return Post::create(2, 'How to create awesome symfony2 application');
    }
}
