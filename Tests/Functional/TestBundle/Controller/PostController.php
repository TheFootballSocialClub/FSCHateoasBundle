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

    public function getCreatePostFormAction(Request $request)
    {
        $form = $this->get('form.factory')->createBuilder('form')
            ->add('title', 'text')
            ->getForm()
        ;
        $formView = $form->createView();

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('form');

        return new Response($this->get('serializer')->serialize($formView, $request->get('_format')));
    }
}
