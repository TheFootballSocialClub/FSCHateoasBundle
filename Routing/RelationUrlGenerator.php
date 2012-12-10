<?php

namespace FSC\HateoasBundle\Routing;

use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Factory\ParametersFactoryInterface;

class RelationUrlGenerator
{
    protected $urlGenerator;
    protected $metadataFactory;
    protected $parametersFactory;

    public function __construct(UrlGenerator $urlGenerator, MetadataFactoryInterface $metadataFactory,
        ParametersFactoryInterface $parametersFactory)
    {
        $this->urlGenerator = $urlGenerator;
        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
    }

    public function generateUrl($object, $rel)
    {
        $metadata = $this->metadataFactory->getMetadata($object);
        $relationMetadata = $metadata->getRelation($rel);

        if (null === $relationMetadata) {
            throw new \RuntimeException(sprintf('Relation "%s" doesn\'t exist.', $rel));
        }

        return $this->urlGenerator->generate(
            $relationMetadata->getRoute(),
            $this->parametersFactory->createParameters($object, $relationMetadata->getParams())
        );
    }
}
