<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FSC\HateoasBundle\Model\RouteAwarePager;

class UserController extends Controller
{
    public function getUserPostsAction(Request $request, $id)
    {
        $postsPager = $this->get('test.provider.post')->getUserPostsPager($id);
        $postsPager->setCurrentPage($request->query->get('page', $postsPager->getCurrentPage()));
        $postsPager->setMaxPerPage($request->query->get('limit', $postsPager->getMaxPerPage()));
        $routeAwarePager = new RouteAwarePager($postsPager, $request->attributes->get('_route'), $request->attributes->get('_route_params'));

        // $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('posts');

        return new Response($this->get('serializer')->serialize($routeAwarePager, $request->get('_format')));
    }
}
