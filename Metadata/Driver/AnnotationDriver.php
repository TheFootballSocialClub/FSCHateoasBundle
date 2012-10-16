<?php

namespace FSC\HateoasBundle\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Doctrine\Common\Annotations\Reader;

use FSC\HateoasBundle\Metadata\ClassMetadata;
use FSC\HateoasBundle\Annotation;

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

        $relations = array();
        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Annotation\Relation) {
                $relation = array(
                    'rel' => $annotation->rel,
                    'route' => $annotation->route,
                    'params' => $annotation->params ?: array(),
                );

                if (!empty($annotation->content)) {
                    $relation['content'] = array(
                        'provider_id' => $annotation->content['provider_id'],
                        'provider_method' => $annotation->content['provider_method'],
                        'serializer_type' => isset($annotation->content['serializer_type']) ? $annotation->content['serializer_type'] : null,
                        'serializer_xml_element_name' => isset($annotation->content['serializer_xml_element_name']) ? $annotation->content['serializer_xml_element_name'] : null,
                        'serializer_xml_element_name_root_metadata' => isset($annotation->content['serializer_xml_element_name_root_metadata']) ? (Boolean) $annotation->content['serializer_xml_element_name_root_metadata'] : false,
                    );
                }

                $relations[] = $relation;
            }
        }
        $classMetadata->setRelations($relations);

        return $classMetadata;
    }
}
