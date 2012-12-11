<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use FSC\HateoasBundle\Model\HalPagerfanta;

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

    public function pagerAction(Request $request)
    {
        $data = array(
            array('first' => 'value'),
            array('second' => 'value')
        );
        $pager = new Pagerfanta(new ArrayAdapter($data));
        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($pager);

        $halPager = HalPagerfanta::create($pager, 'test-rel');

        return new Response($this->get('serializer')->serialize($halPager, $request->get('_format')));
    }
}
