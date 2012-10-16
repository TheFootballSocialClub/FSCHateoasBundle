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
                if (!empty($annotation->params)) {
                    $relationMetadata->setParams($annotation->params);
                }

                if (!empty($annotation->content)) {
                    $relationContentMetadata = new RelationContentMetadata($annotation->content['provider_id'], $annotation->content['provider_method']);
                    $relationMetadata->setContent($relationContentMetadata);

                    if (isset($annotation->content['serializer_type'])) {
                        $relationContentMetadata->setSerializerType($annotation->content['serializer_type']);
                    }

                    if (isset($annotation->content['serializer_xml_element_name'])) {
                        $relationContentMetadata->setSerializerXmlElementName($annotation->content['serializer_xml_element_name']);
                    }

                    if (isset($annotation->content['serializer_xml_element_name_root_metadata'])) {
                        $relationContentMetadata->setSerializerXmlElementRootName($annotation->content['serializer_xml_element_name_root_metadata']);
                    }
                }

                $classMetadata->addRelation($relationMetadata);
            }
        }

        return $classMetadata;
    }
}
