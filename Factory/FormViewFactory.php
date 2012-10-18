<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormViewFactory
{
    protected $urlGenerator;
    protected $formFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, FormFactoryInterface $formFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->formFactory = $formFactory;
    }

    public function create(FormInterface $form, $method, $route, array $routeParameters = array())
    {
        $formView = $form->createView();

        $formView->vars['method'] = strtoupper($method);
        $formView->vars['action'] = $this->urlGenerator->generate($route, $routeParameters, true);

        return $formView;
    }

    public function formFactoryCreate($arguments, $method, $route, array $routeParameters = array())
    {
        $form = call_user_func_array(array($this->formFactory, 'create'), $arguments);

        return $this->create($form, $method, $route, $routeParameters);
    }

    public function formFactoryCreateNamed($arguments, $method, $route, array $routeParameters = array())
    {
        $form = call_user_func_array(array($this->formFactory, 'createNamed'), $arguments);

        return $this->create($form, $method, $route, $routeParameters);
    }
}
