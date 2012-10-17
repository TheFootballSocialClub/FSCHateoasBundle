<?php

namespace FSC\HateoasBundle\Model;

use Pagerfanta\PagerfantaInterface;

/**
 * A handler is registered for this class. It will add self/next/previous/first/last links.
 */
class RouteAwarePager implements PagerfantaInterface
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

    public function getIterator()
    {
        return $this->pager->getIterator();
    }

    function setMaxPerPage($maxPerPage)
    {
        return $this->pager->setMaxPerPage($maxPerPage);
    }

    function getMaxPerPage()
    {
        return $this->pager->getMaxPerPage();
    }

    function setCurrentPage($currentPage)
    {
        return $this->pager->setCurrentPage($currentPage);
    }

    function getCurrentPage()
    {
        return $this->pager->getCurrentPage();
    }

    function getCurrentPageResults()
    {
        return $this->pager->getCurrentPageResults();
    }

    function getNbResults()
    {
        return $this->pager->getNbResults();
    }

    function getNbPages()
    {
        return $this->pager->getNbPages();
    }

    function haveToPaginate()
    {
        return $this->pager->haveToPaginate();
    }

    function hasPreviousPage()
    {
        return $this->pager->hasPreviousPage();
    }

    function getPreviousPage()
    {
        return $this->pager->getPreviousPage();
    }

    function hasNextPage()
    {
        return $this->pager->hasNextPage();
    }

    function getNextPage()
    {
        return $this->pager->getNextPage();
    }

    public function count()
    {
        return $this->pager->count();
    }
}
