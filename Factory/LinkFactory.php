<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\Util\PropertyPath;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

class LinkFactory extends AbstractLinkFactory implements LinkFactoryInterface
{
    protected $metadataFactory;
    protected $parametersFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, MetadataFactoryInterface $metadataFactory,
                                ParametersFactoryInterface $parametersFactory)
    {
        parent::__construct($urlGenerator);

        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
    }

    public function createLinks($object)
    {
        if ($object instanceof Link) {
            return;
        }

        if (null === ($classMetadata = $this->metadataFactory->getMetadata($object))) {
            return;
        }

        return $this->createLinksFromMetadata($classMetadata, $object);
    }

    public function createLinksFromMetadata(ClassMetadataInterface $classMetadata, $object)
    {
        $links = array();

        foreach ($classMetadata->getRelations() as $relationMetadata) {
            $links[] = $this->createLinkFromMetadata($relationMetadata, $object);
        }

        return $links;
    }

    public function createLinkFromMetadata(RelationMetadataInterface $relationMetadata, $object)
    {
        $href = $this->generateUrl($relationMetadata->getRoute(), $this->parametersFactory->createParameters($object, $relationMetadata->getParams()));

        return $this->createLink($relationMetadata->getRel(), $href);
    }
}
