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
        $linksAwareWrapper = $this->get('fsc_hateoas.factory.links_aware_wrapper')->create($postsPager);

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('posts');

        return new Response($this->get('serializer')->serialize($linksAwareWrapper, $request->get('_format')));
    }
}
