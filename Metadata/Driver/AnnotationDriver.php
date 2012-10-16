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

        $links = array();
        foreach ($this->reader->getClassAnnotations($class) as $annotation) {
            if ($annotation instanceof Annotation\Relation) {
                $links[] = array(
                    'rel' => $annotation->rel,
                    'route' => $annotation->route,
                    'params' => $annotation->params ?: array(),
                );
            }
        }
        $classMetadata->setLinks($links);

        return $classMetadata;
    }
}
