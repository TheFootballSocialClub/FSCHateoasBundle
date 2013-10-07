<?php

namespace FSC\HateoasBundle\Metadata\Builder;

use FSC\HateoasBundle\Metadata\RelationMetadata;
use FSC\HateoasBundle\Metadata\RelationContentMetadata;
use Pagerfanta\PagerfantaInterface;

class RelationsBuilder implements RelationsBuilderInterface
{
    /**
     * @var array<RelationMetadataInterface>
     */
    protected $relationsMetadata;

    protected $defaultPageParameterName;
    protected $defaultLimitParameterName;

    public function __construct(
        $defaultPageParameterName = 'page',
        $defaultLimitParameterName = 'limit'
    ) {
        $this->relationsMetadata = array();
        $this->defaultPageParameterName = $defaultPageParameterName;
        $this->defaultLimitParameterName = $defaultLimitParameterName;
    }

    public function add($rel, $href, array $embed = null, array $attributes = null, array $excludeIf = null)
    {
        $relationMetadata = new RelationMetadata($rel);

        if (is_array($href)) {
            if (!isset($href['route'])) {
                throw new \RuntimeException('href\'s "route" is required.');
            }

            $relationMetadata->setRoute($href['route']);

            if (isset($href['parameters'])) {
                $relationMetadata->setParams($href['parameters']);
            }
            if (isset($href['options'])) {
                $relationMetadata->setOptions($href['options']);
            }
        } else {
            $relationMetadata->setUrl($href);
        }

        if (null !== $embed) {
            if (!empty($embed['provider']) && !empty($embed['property'])) {
                throw new \RuntimeException('Content configuration can only have either a provider or a property.');
            }
            if (empty($embed['provider']) && empty($embed['property'])) {
                throw new \RuntimeException('Content configuration needs either a provider or a property.');
            }
            if (isset($embed['provider']) && 2 !== count($embed['provider'])) {
                throw new \RuntimeException('Content "provider" is required, and should be an array of 2 values. [service, method]');
            }

            if (!empty($embed['provider'])) {
                $providerId     = $embed['provider'][0];
                $providerMethod = $embed['provider'][1];
            } else {
                $providerId     = 'fsc_hateoas.factory.identity';
                $providerMethod = 'get';
            }

            $contentMetadata = new RelationContentMetadata($providerId, $providerMethod);

            if (isset($embed['providerArguments'])) {
                $contentMetadata->setProviderArguments($embed['providerArguments']);
            }

            if (isset($embed['property'])) {
                $contentMetadata->setProviderArguments(array($embed['property']));
            }

            if (isset($embed['serializerType'])) {
                $contentMetadata->setSerializerType($embed['serializerType']);
            }

            if (isset($embed['serializerXmlElementName'])) {
                $contentMetadata->setSerializerXmlElementName($embed['serializerXmlElementName']);
            }

            if (isset($embed['serializerXmlElementRootMetadata'])) {
                $contentMetadata->setSerializerXmlElementRootName($embed['serializerXmlElementRootMetadata']);
            }

            $relationMetadata->setContent($contentMetadata);
        }

        if (null !== $attributes) {
            $relationMetadata->setAttributes($attributes);
        }

        if (null !== $excludeIf) {
            $relationMetadata->setExcludeIf($excludeIf);
        }

        $this->relationsMetadata[] = $relationMetadata;
    }
    
    public function addPagerNavigationRelations(PagerfantaInterface $pager, $route, $routeParameters = array(), $pageParameterName = null, $limitParameterName = null)
    {
        if (null === $pageParameterName) {
            $pageParameterName = $this->defaultPageParameterName;
        }
        if (null === $limitParameterName) {
            $limitParameterName = $this->defaultLimitParameterName;
        }

        if (!isset($routeParameters[$pageParameterName])) {
            $routeParameters[$pageParameterName] = $pager->getCurrentPage();
        }
        if (!isset($routeParameters[$limitParameterName])) {
            $routeParameters[$limitParameterName] = $pager->getMaxPerPage();
        }

        $this->add('self', array(
            'route' => $route,
            'parameters' => $routeParameters,
        ));
        $this->add('first', array(
            'route' => $route,
            'parameters' => array_merge($routeParameters, array($pageParameterName => '1'))
        ));

        $this->add('last', array(
            'route' => $route,
            'parameters' => array_merge($routeParameters, array($pageParameterName => ($pager->getNbPages()>0)?$pager->getNbPages():1))
        ));

        if ($pager->hasPreviousPage()) {
            $this->add('previous', array(
                'route' => $route,
                'parameters' => array_merge($routeParameters, array($pageParameterName => $pager->getPreviousPage()))
            ));
        }

        if ($pager->hasNextPage()) {
            $this->add('next', array(
                'route' => $route,
                'parameters' => array_merge($routeParameters, array($pageParameterName => $pager->getNextPage()))
            ));
        }
    }

    public function build()
    {
        return $this->relationsMetadata;
    }

    public function clear()
    {
        $this->relationsMetadata = array();
    }
}
