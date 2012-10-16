<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Pagerfanta\PagerfantaInterface;

class PagerLinkFactory extends AbstractLinkFactory implements PagerLinkFactoryInterface
{
    protected $pageParameterName;
    protected $limitParameterName;

    public function __construct(UrlGeneratorInterface $urlGenerator, $pageParameterName = null, $limitParameterName = null)
    {
        parent::__construct($urlGenerator);

        $this->pageParameterName = $pageParameterName ?: 'page';
        $this->limitParameterName = $limitParameterName ?: 'limit';
    }

    public function createPagerLinks(PagerfantaInterface $pager, $route, $defaultRouteParameters)
    {
        if (!isset($defaultRouteParameters[$this->pageParameterName])) {
            $defaultRouteParameters[$this->pageParameterName] = $pager->getCurrentPage();
        }
        if (!isset($defaultRouteParameters[$this->limitParameterName])) {
            $defaultRouteParameters[$this->limitParameterName] = $pager->getMaxPerPage();
        }

        $links = array();
        $links[] = $this->createLink('self', $this->generateUrl($route, $defaultRouteParameters));
        $links[] = $this->createLink('first', $this->generateUrl(
            $route,
            array_merge($defaultRouteParameters, array($this->pageParameterName => '1'))
        ));

        $links[] = $this->createLink('last', $this->generateUrl(
            $route,
            array_merge($defaultRouteParameters, array($this->pageParameterName => $pager->getNbPages()))
        ));

        if ($pager->hasPreviousPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($defaultRouteParameters, array($this->pageParameterName => $pager->getPreviousPage()))
            ));
        }

        if ($pager->hasNextPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($defaultRouteParameters, array($this->pageParameterName => $pager->getNextPage()))
            ));
        }

        return $links;
    }
}
