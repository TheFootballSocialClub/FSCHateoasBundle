<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\PostsCollection;

class PostController extends Controller
{
    public function listPostsAction(Request $request)
    {
        $postsPager = $this->get('test.provider.post')->getPostsPager();

        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($postsPager); // Automatically add self/first/last/prev/next links

        $postsCollection = new PostsCollection($postsPager); // Class that holds relations data (ie: create form relation)

        return new Response($this->get('serializer')->serialize($postsCollection, $request->get('_format')));
    }

    public function getPostAction(Request $request, $id)
    {
        $post = $this->get('test.provider.post')->getPost($id);

        return new Response($this->get('serializer')->serialize($post, $request->get('_format')));
    }

    public function getCreatePostFormAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamed('post', 'test_post_create');
        $formView = $this->get('fsc_hateoas.factory.form_view')->create($form, 'POST', 'api_post_create'); // Create form view and add method/action data to the FormView

        $this->get('fsc_hateoas.metadata.relations_manager')->addBasicRelations($formView); // Automatically add self links to the form

        return new Response($this->get('serializer')->serialize($formView, $request->get('_format')));
    }

    public function getCreateFormatPostFormAction(Request $request)
    {
        $this->get('fsc_hateoas.routing.generator')->setExtraParameters(array(
            '_format' => $request->get('_format'),
        ));

        return $this->getCreatePostFormAction($request);
    }

    public function putPostAction($id)
    {
        $post = $this->get('test.provider.post')->getPost($id);

        return new Response('', 201, array(
            'Location' => $this->get('fsc_hateoas.routing.relation_url_generator')->generateUrl($post, 'self'),
        ));
    }

    public function getPostAlternateRouterAction(Request $request, $id)
    {
        $post = $this->get('test.provider.post')->getAlternateRouterPost($id);

        return new Response($this->get('serializer')->serialize($post, $request->get('_format')));
    }

    public function getPostTemplatedAction(Request $request, $id)
    {
        $post = $this->get('test.provider.post')->getPostTemplated($id);

        return new Response($this->get('serializer')->serialize($post, $request->get('_format')));
    }
}
