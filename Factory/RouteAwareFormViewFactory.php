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

    public function create($type, $method, $route, $routeParameters = array())
    {
        $form = $this->formFactory->create($type);
        $formView = $form->createView();

        return $this->createRouteAwareFormView($formView, $method, $route, $routeParameters);
    }

    public function createNamed($name, $type, $method, $route, $routeParameters = array())
    {
        $form = $this->formFactory->createNamed($name, $type);
        $formView = $form->createView();

        return $this->createRouteAwareFormView($formView, $method, $route, $routeParameters);
    }

    public function createRouteAwareFormView(FormView $formView, $method, $route, $routeParameters)
    {
        return new RouteAwareFormView($formView, $method, $route, $routeParameters);
    }
}
