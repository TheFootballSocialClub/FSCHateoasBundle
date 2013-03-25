<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;
use FSC\HateoasBundle\Routing\RelationUrlGenerator;

class LinkFactory extends AbstractLinkFactory implements LinkFactoryInterface
{
    protected $metadataFactory;
    protected $parametersFactory;

    public function __construct(MetadataFactoryInterface $metadataFactory,
                                ParametersFactoryInterface $parametersFactory,
                                RelationUrlGenerator $relationUrlGenerator
    ) {
        parent::__construct($relationUrlGenerator);

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
        if (null !== $relationMetadata->getUrl()) {
            $href = $relationMetadata->getUrl();
        } else {
            $href = $this->generateUrl(
                $relationMetadata->getRoute(),
                $this->parametersFactory->createParameters($object, $relationMetadata->getParams()),
                $relationMetadata->getOptions()
            );
        }

        return $this->createLink($relationMetadata->getRel(), $href, $relationMetadata->getTemplated());
    }
}
