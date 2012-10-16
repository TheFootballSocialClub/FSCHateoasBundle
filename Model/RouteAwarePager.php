<?php

namespace FSC\HateoasBundle\Model;

use Pagerfanta\PagerfantaInterface;

class RouteAwarePager
{
    protected $pager;
    protected $route;
    protected $routeParameters;

    public function __construct(PagerfantaInterface $pager, $route, $routeParameters)
    {
        $this->pager = $pager;
        $this->route = $route;
        $this->routeParameters = $routeParameters;
    }

    public function getPager()
    {
        return $this->pager;
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
