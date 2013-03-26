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
    protected $forceAbsolute;

    public function __construct(MetadataFactoryInterface $metadataFactory, ParametersFactoryInterface $parametersFactory, $forceAbsolute = true)
    {
        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
        $this->urlGenerators = array();
        $this->forceAbsolute = $forceAbsolute;
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

        $absolute = $this->forceAbsolute;
        if (isset($options['absolute'])) {
            $absolute = $options['absolute'];
        }

        return $urlGenerator->generate(
            $relationMetadata->getRoute(),
            $this->parametersFactory->createParameters($object, $relationMetadata->getParams()),
            $absolute
        );
    }

    /**
     * Adds a URL Generator to the internal list of generators. Called by the Compiler Pass
     * @param string                $alias
     * @param UrlGeneratorInterface $generator
     */
    public function setUrlGenerator($alias, UrlGeneratorInterface $generator)
    {
        $this->urlGenerators[$alias] = $generator;
    }

    /**
     * @param  string $alias
     * @return UrlGeneratorInterface
     * @throws InvalidArgumentException
     */
    public function getUrlGenerator($alias)
    {
        if (empty($this->urlGenerators[$alias])) {
            throw new \InvalidArgumentException("URL Generator with alias {$alias} not found");
        }

        return $this->urlGenerators[$alias];
    }
}
