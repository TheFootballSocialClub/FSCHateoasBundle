<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function getUserPostsAction(Request $request, $id)
    {
        $postsPager = $this->get('test.provider.post')->getUserPostsPager($id);
        $postsPager->setCurrentPage($request->query->get('page', $postsPager->getCurrentPage()));
        $postsPager->setMaxPerPage($request->query->get('limit', $postsPager->getMaxPerPage()));

        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($postsPager); // Automatically add self/first/last/prev/next links

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('posts');

        return new Response($this->get('serializer')->serialize($postsPager, $request->get('_format')));
    }
}
