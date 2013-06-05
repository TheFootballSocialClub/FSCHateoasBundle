<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;

class UserController extends Controller
{
    public function getUserPostsAction(Request $request, $id)
    {
        $postsPager = $this->get('test.provider.post')->getUserPostsPager($id);
        $postsPager->setCurrentPage($request->query->get('page', $postsPager->getCurrentPage()));
        $postsPager->setMaxPerPage($request->query->get('limit', $postsPager->getMaxPerPage()));

        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($postsPager); // Automatically add self/first/last/prev/next links

        return new Response($this->get('serializer')->serialize($postsPager, $request->get('_format')));
    }

    public function getUsersProxyPagerAction(Request $request)
    {
        require __DIR__ . "/../Model/UserProxy.php";

        $user1 = new \Proxies\__CG__\FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;
        $user2 = User::create(24, 'Adrien', 'Brault');

        $results = array(
            $user1,
            $user2
        );

        $pager = new Pagerfanta(new ArrayAdapter($results));

        return new Response($this->get('serializer')->serialize($pager, $request->get('_format')));
    }
}
