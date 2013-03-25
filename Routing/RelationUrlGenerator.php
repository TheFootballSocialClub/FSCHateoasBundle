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
            $options
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
