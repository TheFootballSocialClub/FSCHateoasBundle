<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

class MixedController extends Controller
{
    public function listAction(Request $request)
    {
        $results = array(
            $this->get('test.provider.post')->getPost(1),
            $this->get('test.provider.post')->getPost(2),
            $this->get('test.provider.user')->getUser(1),
        );

        $pager = new Pagerfanta(new ArrayAdapter($results));

        return new Response($this->get('serializer')->serialize($pager, $request->get('_format')));
    }
}
