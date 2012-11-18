<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\Root;

class RootController extends Controller
{
    public function indexAction(Request $request)
    {
        $root = new Root();

        if ($request->query->has('user_id')) {
            $relationsBuilder = $this->get('fsc_hateoas.metadata.relation_builder.factory')->create();
            $relationsBuilder->add('me', array(
                'route' => 'api_user_get',
                'parameters' => array('identifier' => $request->query->get('user_id'))
            ));

            $this->get('fsc_hateoas.metadata.factory')->addObjectRelations($root, $relationsBuilder->build());
        }

        return new Response($this->get('serializer')->serialize($root, $request->get('_format')));
    }
}
