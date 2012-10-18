<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;

use FSC\HateoasBundle\Model\RouteAwareFormView;

class RouteAwareFormViewFactory
{
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function formFactoryCreate(array $createArguments, $method, $route, $routeParameters = array())
    {
        $form = call_user_func_array(array($this->formFactory, 'create'), $createArguments);
        $formView = $form->createView();

        return $this->create($formView, $method, $route, $routeParameters);
    }

    public function formFactoryCreateNamed($createArguments, $method, $route, $routeParameters = array())
    {
        $form = call_user_func_array(array($this->formFactory, 'createNamed'), $createArguments);
        $formView = $form->createView();

        return $this->create($formView, $method, $route, $routeParameters);
    }

    public function create(FormView $formView, $method, $route, $routeParameters)
    {
        return new RouteAwareFormView($formView, $method, $route, $routeParameters);
    }
}
