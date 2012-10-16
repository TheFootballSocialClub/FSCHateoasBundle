<?php

namespace FSC\HateoasBundle\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Doctrine\Common\Annotations\Reader;

use FSC\HateoasBundle\Annotation;
use FSC\HateoasBundle\Metadata\ClassMetadata;
use FSC\HateoasBundle\Metadata\RelationMetadata;
use FSC\HateoasBundle\Metadata\RelationContentMetadata;

class AnnotationDriver implements DriverInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($name = $class->getName());
        $classMetadata->fileResources[] = $class->getFilename();

        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Annotation\Relation) {
                $relationMetadata = new RelationMetadata($annotation->rel, $annotation->route);
                if (!empty($annotation->parameters)) {
                    $relationMetadata->setParams($annotation->parameters);
                }

                if (!empty($annotation->content)) {
                    $relationContentMetadata = new RelationContentMetadata($annotation->content['providerId'], $annotation->content['providerMethod']);
                    $relationMetadata->setContent($relationContentMetadata);

                    if (isset($annotation->content['providerParameters'])) {
                        $relationContentMetadata->setProviderParameters($annotation->content['providerParameters']);
                    }

                    if (isset($annotation->content['serializerType'])) {
                        $relationContentMetadata->setSerializerType($annotation->content['serializerType']);
                    }

                    if (isset($annotation->content['serializerXmlElementName'])) {
                        $relationContentMetadata->setSerializerXmlElementName($annotation->content['serializerXmlElementName']);
                    }

                    if (isset($annotation->content['serializerXmlElementNameRootMetadata'])) {
                        $relationContentMetadata->setSerializerXmlElementRootName($annotation->content['serializerXmlElementNameRootMetadata']);
                    }
                }

                $classMetadata->addRelation($relationMetadata);
            }
        }

        return $classMetadata;
    }
}
