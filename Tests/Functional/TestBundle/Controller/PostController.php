<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use FSC\HateoasBundle\Model\RouteAwarePager;
use FSC\HateoasBundle\Model\RouteAwareFormView;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\PostsCollection;

class PostController extends Controller
{
    public function listPostsAction(Request $request)
    {
        $postsPager = $this->get('test.provider.post')->getPostsPager();
        $routeAwarePager = new RouteAwarePager($postsPager, $request->attributes->get('_route'), $request->attributes->get('_route_params'));
        $postsCollection = new PostsCollection($routeAwarePager);

        return new Response($this->get('serializer')->serialize($postsCollection, $request->get('_format')));
    }

    public function getPostAction(Request $request, $id)
    {
        $post = $this->get('test.provider.post')->getPost($id);

        return new Response($this->get('serializer')->serialize($post, $request->get('_format')));
    }

    public function getCreatePostFormAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamed('post', 'test_post_create');
        $formView = $form->createView();
        $routeAwareFormView = new RouteAwareFormView($formView, 'POST', 'api_post_create');

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('form');

        return new Response($this->get('serializer')->serialize($routeAwareFormView, $request->get('_format')));
    }
}
