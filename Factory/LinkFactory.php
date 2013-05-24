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
                                RelationUrlGenerator $relationUrlGenerator,
                                $forceAbsolute = true
    ) {
        parent::__construct($relationUrlGenerator, $forceAbsolute);

        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
    }

    public function createLinks($object)
    {
        if ($object instanceof Link) {
            return;
        }

        $classMetadata = $this->metadataFactory->getMetadata($object);
        if (null === $classMetadata) {
            return;
        }

        return $this->createLinksFromMetadata($classMetadata, $object);
    }

    public function createLinksFromMetadata(ClassMetadataInterface $classMetadata, $object)
    {
        $links = array();

        /**
         * @var RelationMetadataInterface $relationMetadata
         */
        foreach ($classMetadata->getRelations() as $relationMetadata) {
            if (!$this->parametersFactory->createExclude($object, $relationMetadata->getExcludeIf())
                && $link = $this->createLinkFromMetadata($relationMetadata, $object)
            ) {
                $links[] = $link;
            }
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

        return $this->createLink($relationMetadata->getRel(), $href, $relationMetadata->getAttributes());
    }
}
