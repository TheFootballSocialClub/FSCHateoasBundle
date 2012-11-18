<?php

namespace FSC\HateoasBundle\Metadata;

use Pagerfanta\PagerfantaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Metadata\Builder\RelationsBuilderFactory;

/**
 * Convenient class to interact with the metadata factory to add metadata at runtime
 */
class RelationsManager implements RelationsManagerInterface
{
    protected $metadataFactory;
    protected $container;
    protected $relationsBuilderFactory;

    public function __construct(MetadataFactoryInterface $metadataFactory,
        RelationsBuilderFactory $relationsBuilderFactory, ContainerInterface $container)
    {
        $this->metadataFactory = $metadataFactory;
        $this->relationsBuilderFactory = $relationsBuilderFactory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function addBasicRelations($object, $route = null, $routeParameters = array())
    {
        if (!is_object($object) || null !== $this->metadataFactory->getMetadata($object)) {
            return;
        }

        if (null === $route) {
            $route = $this->getRequestRoute();
            $routeParameters = $this->getRequestParameters();
        }

        if ($object instanceof PagerfantaInterface) {
            $relations = $this->createPagerNavigationRelations($object, $route, $routeParameters);
        } else {
            $relationsBuilder = $this->relationsBuilderFactory->create();
            $relationsBuilder->add('self', array(
                'route' => $route,
                'parameters' => $routeParameters,
            ));
            $relations = $relationsBuilder->build();
        }

        $this->metadataFactory->addObjectRelations($object, $relations);
    }

    protected function getRequestRoute()
    {
        return $this->container->get('request')->attributes->get('_route');
    }

    protected function getRequestParameters()
    {
        return $this->container->get('request')->attributes->get('_route_params');
    }

    protected function createPagerNavigationRelations(PagerfantaInterface $pager, $route, $routeParameters = array(), $pageParameterName = 'page', $limitParameterName = 'limit')
    {
        if (!isset($routeParameters[$pageParameterName])) {
            $routeParameters[$pageParameterName] = $pager->getCurrentPage();
        }
        if (!isset($routeParameters[$limitParameterName])) {
            $routeParameters[$limitParameterName] = $pager->getMaxPerPage();
        }

        $relationsBuilder = $this->relationsBuilderFactory->create();
        $relationsBuilder->add('self', array(
            'route' => $route,
            'parameters' => $routeParameters,
        ));
        $relationsBuilder->add('first', array(
            'route' => $route,
            'parameters' => array_merge($routeParameters, array($pageParameterName => '1'))
        ));

        $relationsBuilder->add('last', array(
            'route' => $route,
            'parameters' => array_merge($routeParameters, array($pageParameterName => $pager->getNbPages()))
        ));

        if ($pager->hasPreviousPage()) {
            $relationsBuilder->add('previous', array(
                'route' => $route,
                'parameters' => array_merge($routeParameters, array($pageParameterName => $pager->getPreviousPage()))
            ));
        }

        if ($pager->hasNextPage()) {
            $relationsBuilder->add('next', array(
                'route' => $route,
                'parameters' => array_merge($routeParameters, array($pageParameterName => $pager->getNextPage()))
            ));
        }

        return $relationsBuilder->build();
    }
}
