<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use FSC\HateoasBundle\Model\Link;
use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;
use FSC\HateoasBundle\Routing\RelationUrlGenerator;

class LinkFactory extends AbstractLinkFactory implements LinkFactoryInterface
{
    protected $propertyAccessor;
    protected $metadataFactory;
    protected $parametersFactory;

    public function __construct(MetadataFactoryInterface $metadataFactory,
                                ParametersFactoryInterface $parametersFactory,
                                RelationUrlGenerator $relationUrlGenerator,
                                PropertyAccessorInterface $propertyAccessor,
                                $forceAbsolute = true
    ) {
        parent::__construct($relationUrlGenerator, $forceAbsolute);

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
            if (!$this->shouldExcludeLink($relationMetadata, $object) &&
                $link = $this->createLinkFromMetadata($relationMetadata, $object)
            ) {
                $links[] = $link;
            }
        }

        return $links;
    }

    protected function shouldExcludeLink(RelationMetadataInterface $relationMetadata, $object)
    {
        if (!$fields = $relationMetadata->getExcludeIf()) {
            return false;
        }

        foreach ($fields as $field => $valueToExclude) {
            $field = trim($field, '.');
            $propertyPath = new PropertyPath($field);
            $value = $this->propertyAccessor->getValue($object, $propertyPath);

            if ($valueToExclude === $value) {
                return true;
            }
        }

        return false;
    }

    public function createLinkFromMetadata(RelationMetadataInterface $relationMetadata, $object)
    {
        if (null !== $relationMetadata->getUrl()) {
            $href = $relationMetadata->getUrl();

            if (in_array(substr($href, 0, 1), array('.', '['))) {
                $propertyPath = new PropertyPath(preg_replace('/^\./', '', $href));
                $href = $this->propertyAccessor->getValue($object, $propertyPath);
            }
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
