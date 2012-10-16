<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Form\Util\PropertyPath;
use Pagerfanta\PagerfantaInterface;

class PagerLinkFactory extends AbstractLinkFactory implements PagerLinkFactoryInterface
{
    public function createPagerLinks(PagerfantaInterface $pager, $route, $defaultRouteParameters)
    {
        if (!isset($defaultRouteParameters['page'])) {
            $defaultRouteParameters['page'] = $pager->getCurrentPage();
        }
        if (!isset($defaultRouteParameters['limit'])) {
            $defaultRouteParameters['limit'] = $pager->getMaxPerPage();
        }

        $links = array();
        $links[] = $this->createLink('self', $this->generateUrl($route, $defaultRouteParameters));
        $links[] = $this->createLink('first', $this->generateUrl(
            $route,
            array_merge($defaultRouteParameters, array('page' => '1'))
        ));
        $links[] = $this->createLink('last', $this->generateUrl(
            $route,
            array_merge($defaultRouteParameters, array('page' => $pager->getNbPages()))
        ));

        if ($pager->hasPreviousPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($defaultRouteParameters, array('page' => $pager->getPreviousPage()))
            ));
        }

        if ($pager->hasNextPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($defaultRouteParameters, array('page' => $pager->getNextPage()))
            ));
        }

        return $links;
    }
}
