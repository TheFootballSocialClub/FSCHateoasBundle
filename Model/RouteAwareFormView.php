<?php

namespace FSC\HateoasBundle\Model;

use Symfony\Component\Form\FormView;

/**
 * A handler is registered for this class. It will add self method and action
 */
class RouteAwareFormView
{
    protected $formView;
    protected $method;
    protected $route;
    protected $routeParameters;

    public function __construct(FormView $formView, $method, $route, $routeParameters = array())
    {
        $this->formView = $formView;
        $this->method = $method;
        $this->route = $route;
        $this->routeParameters = $routeParameters;
    }

    public function getFormView()
    {
        return $this->formView;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRouteParameters()
    {
        return $this->routeParameters;
    }
}
