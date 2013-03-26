<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Provider;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\Post;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\AlternateRouterPost;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\PostTemplated;

class PostProvider
{
    public function getUserPostsPager($id, $page = 1, $limit = 10)
    {
        // Ie, in real life there would be a database query
        // That would then return a pager

        $posts = array(
            $this->getPost(2),
            $this->getPost(1),
        );

        $pager = new Pagerfanta(new ArrayAdapter($posts));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);

        return $pager;
    }

    public function getUserLastPost($id)
    {
        return $this->getPost(2);
    }

    public function getPost($id)
    {
        switch ($id) {
            case 1:
                return Post::create($id, 'Welcome on the blog!');
            case 2:
                return Post::create($id, 'How to create awesome symfony2 application');
            default:
                return Post::create($id, '');
        }
    }

    public function getPostsPager($page = 1, $limit = 10)
    {
        $posts = array(
            $this->getPost(1),
            $this->getPost(2),
            $this->getPost(3),
        );

        $pager = new Pagerfanta(new ArrayAdapter($posts));
        $pager->setCurrentPage($page);
        $pager->setMaxPerPage($limit);

        return $pager;
    }

    public function getAlternateRouterPost($id)
    {
        switch ($id) {
            case 1:
                return AlternateRouterPost::create($id, 'Welcome on the blog!');
            case 2:
                return AlternateRouterPost::create($id, 'How to create awesome symfony2 application');
            default:
                return AlternateRouterPost::create($id, '');
        }
    }

    public function getPostTemplated($id)
    {
        switch ($id) {
            case 1:
                return PostTemplated::create($id, 'Welcome on the blog!');
            case 2:
                return PostTemplated::create($id, 'How to create awesome symfony2 application');
            default:
                return PostTemplated::create($id, '');
        }
    }
}
