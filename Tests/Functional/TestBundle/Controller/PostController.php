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

        $linksAwareWrapper = $this->get('fsc_hateoas.factory.links_aware_wrapper')->create($postsPager); // Automatically add self/first/last/prev/next links
        $postsCollection = new PostsCollection($linksAwareWrapper); // Class that holds relations data (ie: create form relation)

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
        $formView = $this->get('fsc_hateoas.factory.form_view')->create($form, 'POST', 'api_post_create'); // Add method/action data to the FormView
        $linksAwareWrapper = $this->get('fsc_hateoas.factory.links_aware_wrapper')->create($formView); // Automatically add self links to the form

        $this->get('serializer')->getSerializationVisitor('xml')->setDefaultRootName('form');

        return new Response($this->get('serializer')->serialize($linksAwareWrapper, $request->get('_format')));
    }
}
