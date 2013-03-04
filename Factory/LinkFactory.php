<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

use FSC\HateoasBundle\Exception\RelationRequiredException;

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
            return null;
        }

        if (null === ($classMetadata = $this->metadataFactory->getMetadata($object))) {
            return null;
        }

        return $this->createLinksFromMetadata($classMetadata, $object);
    }

    public function createLinksFromMetadata(ClassMetadataInterface $classMetadata, $object)
    {
        $links = array();

        foreach ($classMetadata->getRelations() as $relationMetadata) {
            /**
             * @var RelationMetadataInterface $relationMetadata
             */
            $link = $this->createLinkFromMetadata($relationMetadata, $object);

            if (isset($link) && $link->getHref()) {
                $links[] = $link;
            } elseif($relationMetadata->getRequired()) {
                throw new RelationRequiredException($relationMetadata, $object);
            }
        }

        return $links;
    }

    public function createLinkFromMetadata(RelationMetadataInterface $relationMetadata, $object)
    {
        try{
            $href = $relationMetadata->getUrl() !== null
                ? $relationMetadata->getUrl()
                : $this->generateUrl($relationMetadata->getRoute(), $this->parametersFactory->createParameters($object, $relationMetadata->getParams()))
            ;
        } catch (UnexpectedTypeException $e) {
            return null;
        }

        return $this->createLink($relationMetadata->getRel(), $href);
    }
}
