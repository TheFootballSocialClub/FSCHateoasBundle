<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PostController extends Controller
{
    public function getPostAction(Request $request, $id)
    {
        $post = $this->get('test.provider.post')->getPost($id);

        return new Response($this->get('serializer')->serialize($post, $request->get('_format')));
    }
}
