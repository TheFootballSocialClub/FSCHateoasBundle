<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

class LinkFactory extends AbstractLinkFactory implements LinkFactoryInterface
{
    protected $propertyAccessor;
    protected $metadataFactory;
    protected $parametersFactory;

    public function __construct(UrlGeneratorInterface $urlGenerator, PropertyAccessorInterface $propertyAccessor,
                                MetadataFactoryInterface $metadataFactory, ParametersFactoryInterface $parametersFactory)
    {
        parent::__construct($urlGenerator);

        $this->propertyAccessor = $propertyAccessor;

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

        /**
         * @var RelationMetadataInterface $relationMetadata
         */
        foreach ($classMetadata->getRelations() as $relationMetadata) {
            if($link = $this->createLinkFromMetadata($relationMetadata, $object)){
                $links[] = $link;
            }
        }

        return $links;
    }

    protected function isSkipLink(RelationMetadataInterface $relationMetadata, $object){
        if(! $fields = $relationMetadata->getSkipIfNull()){
            return false;
        }

        foreach($fields as $field){
            $field = trim($field, '.');
            $propertyPath = new PropertyPath($field);
            $value = $this->propertyAccessor->getValue($object, $propertyPath);

            if(null === $value){
                return true;
            }
        }

        return false;
    }

    public function createLinkFromMetadata(RelationMetadataInterface $relationMetadata, $object)
    {
        if($this->isSkipLink($relationMetadata, $object)){
            return null;
        }

        $href = $relationMetadata->getUrl() !== null
            ? $relationMetadata->getUrl()
            : $this->generateUrl($relationMetadata->getRoute(), $this->parametersFactory->createParameters($object, $relationMetadata->getParams()))
        ;

        return $this->createLink($relationMetadata->getRel(), $href);
    }
}
