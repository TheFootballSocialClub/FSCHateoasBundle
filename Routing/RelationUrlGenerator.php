<?php

namespace FSC\HateoasBundle\Routing;

use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Factory\ParametersFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RelationUrlGenerator
{
    protected $metadataFactory;
    protected $parametersFactory;
    protected $urlGenerators;

    public function __construct(MetadataFactoryInterface $metadataFactory, ParametersFactoryInterface $parametersFactory)
    {
        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
        $this->urlGenerators = array();
    }

    public function generateUrl($object, $rel)
    {
        $metadata = $this->metadataFactory->getMetadata($object);
        $relationMetadata = $metadata->getRelation($rel);

        if (null === $relationMetadata) {
            throw new \RuntimeException(sprintf('Relation "%s" doesn\'t exist.', $rel));
        }

        $options = $relationMetadata->getOptions();
        $alias = !empty($options['router']) ? $options['router'] : 'default';
        $urlGenerator = $this->getUrlGenerator($alias);

        return $urlGenerator->generate(
            $relationMetadata->getRoute(),
            $this->parametersFactory->createParameters($object, $relationMetadata->getParams()),
            false,                                                                                  // absolute, TODO move that to options as well
            $options
        );
    }

    /**
     * Adds a URL Generator to the internal list of generators. Called by the Compiler Pass
     * @param String                $alias
     * @param UrlGeneratorInterface $generator
     * @throws RuntimeException
     */
    public function addUrlGenerator($alias, UrlGeneratorInterface $generator)
    {
        if (!empty($this->urlGenerators[$alias])) {
            throw new \RuntimeException("You can only have one URL Generator service with alias {$alias}");
        }

        $this->urlGenerators[$alias] = $generator;
    }

    /**
     * @param  String $alias
     * @return UrlGeneratorInterface
     * @throws RuntimeException
     */
    public function getUrlGenerator($alias)
    {
        if (empty($this->urlGenerators[$alias])) {
            throw new \RuntimeException("URL Generator with alias {$alias} not found");
        }

        return $this->urlGenerators[$alias];
    }
}
