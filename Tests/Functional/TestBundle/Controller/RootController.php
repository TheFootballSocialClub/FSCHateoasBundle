<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\Root;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\EmptyRoot;

class RootController extends Controller
{
    public function indexAction(Request $request)
    {
        $root = new Root();

        if ($request->query->has('user_id')) {
            $this->get('fsc_hateoas.metadata.relations_manager')->addRelation($root, 'me', array(
                'route' => 'api_user_get',
                'parameters' => array('identifier' => $request->query->get('user_id'))
            ));
        }

        $this->get('fsc_hateoas.metadata.relations_manager')->addRelation($root, 'adrienbrault', 'http://adrienbrault.fr');

        return new Response($this->get('serializer')->serialize($root, $request->get('_format')));
    }

    public function disabledLinksAction(Request $request)
    {
        $root = new Root();

        $this->get('fsc_hateoas.metadata.relations_manager')->addRelation($root, 'adrienbrault', 'http://adrienbrault.fr');
        $this->get('fsc_hateoas.serializer.metadata_helper')->disableLinks();

        return new Response($this->get('serializer')->serialize($root, $request->get('_format')));
    }

    public function emptyAction(Request $request)
    {
        $data = new EmptyRoot();

        return new Response($this->get('serializer')->serialize($data, $request->get('_format')));
    }
}
