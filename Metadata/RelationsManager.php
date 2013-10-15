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
    protected $defaultPageParameterName;
    protected $defaultLimitParameterName;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        RelationsBuilderFactory $relationsBuilderFactory,
        ContainerInterface $container
    ) {
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

    public function addRelation($object, $rel, $href, array $embed = null)
    {
        $relationsBuilder = $this->relationsBuilderFactory->create();
        $relationsBuilder->add($rel, $href, $embed);

        $this->metadataFactory->addObjectRelations($object, $relationsBuilder->build());
    }

    protected function getRequestRoute()
    {
        return $this->container->get('request')->attributes->get('_route');
    }

    protected function getRequestParameters()
    {
        $request       = $this->container->get('request');

        return array_merge($request->attributes->get('_route_params'), $request->query->all());
    }

    protected function createPagerNavigationRelations(PagerfantaInterface $pager, $route, $routeParameters = array(), $pageParameterName = null, $limitParameterName = null)
    {
        $relationsBuilder = $this->relationsBuilderFactory->create();
        $relationsBuilder->addPagerNavigationRelations($pager, $route, $routeParameters, $pageParameterName, $limitParameterName);

        return $relationsBuilder->build();
    }
}
