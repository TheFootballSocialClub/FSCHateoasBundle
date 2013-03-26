<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FormViewFactory
{
    protected $urlGenerator;
    protected $formFactory;
    protected $forceAbsolute;

    public function __construct(UrlGeneratorInterface $urlGenerator, FormFactoryInterface $formFactory, $forceAbsolute = true)
    {
        $this->urlGenerator = $urlGenerator;
        $this->formFactory = $formFactory;
        $this->forceAbsolute = $forceAbsolute;
    }

    public function create(FormInterface $form, $method, $route, array $routeParameters = array())
    {
        $formView = $form->createView();

        $formView->vars['attr'] = array(
            'method' => strtoupper($method),
            'action' => $this->urlGenerator->generate($route, $routeParameters, $this->forceAbsolute),
        );

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
