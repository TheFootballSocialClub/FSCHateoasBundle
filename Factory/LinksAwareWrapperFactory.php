<?php

namespace FSC\HateoasBundle\Factory;

use Pagerfanta\PagerfantaInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Metadata\MetadataFactoryInterface;

use FSC\HateoasBundle\Factory\AbstractLinkFactory;
use FSC\HateoasBundle\Model\LinksAwareWrapper;

class LinksAwareWrapperFactory extends AbstractLinkFactory implements LinksAwareWrapperFactoryInterface
{
    protected $metadataFactory;
    protected $container;

    public function __construct(UrlGeneratorInterface $urlGenerator, MetadataFactoryInterface $metadataFactory,
                                ContainerInterface $container)
    {
        parent::__construct($urlGenerator);

        $this->metadataFactory = $metadataFactory;
        $this->container = $container;
    }

    public function create($data, $route = null, $routeParameters = array())
    {
        if (is_object($data) && null !== $this->metadataFactory->getMetadataForClass(get_class($data))) {
            return;
        }

        if ($data instanceof PagerfantaInterface) {
            $links = $this->createPagerNavigationLinks($data, $route, $routeParameters);
        } else {
            $links = array(
                $this->createLink('self', $this->generateUrl($route, $routeParameters))
            );
        }

        return $this->createLinksAwareWrapper($data, $links);
    }

    public function generateUrl($name, $parameters = array())
    {
        if (null === $name && $this->container->isScopeActive('request')) {
            $request = $this->container->get('request');
            $name = $request->attributes->get('_route');
            $parameters = array_merge($request->attributes->get('_route_params'), $parameters);
        }

        return parent::generateUrl($name, $parameters);
    }

    protected function createLinksAwareWrapper($data, array $links)
    {
        return new LinksAwareWrapper($data, $links);
    }

    protected function createPagerNavigationLinks(PagerfantaInterface $pager, $route, $routeParameters = array(), $pageParameterName = 'page', $limitParameterName = 'limit')
    {
        if (!isset($routeParameters[$pageParameterName])) {
            $routeParameters[$pageParameterName] = $pager->getCurrentPage();
        }
        if (!isset($routeParameters[$limitParameterName])) {
            $routeParameters[$limitParameterName] = $pager->getMaxPerPage();
        }

        $links = array();
        $links[] = $this->createLink('self', $this->generateUrl($route, $routeParameters));
        $links[] = $this->createLink('first', $this->generateUrl(
            $route,
            array_merge($routeParameters, array($pageParameterName => '1'))
        ));

        $links[] = $this->createLink('last', $this->generateUrl(
            $route,
            array_merge($routeParameters, array($pageParameterName => $pager->getNbPages()))
        ));

        if ($pager->hasPreviousPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($routeParameters, array($pageParameterName => $pager->getPreviousPage()))
            ));
        }

        if ($pager->hasNextPage()) {
            $links[] = $this->createLink('next', $this->generateUrl(
                $route,
                array_merge($routeParameters, array($pageParameterName => $pager->getNextPage()))
            ));
        }

        return $links;
    }
}
