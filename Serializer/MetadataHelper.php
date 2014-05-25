<?php

namespace FSC\HateoasBundle\Serializer;

use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactory;

class MetadataHelper
{
    protected $serializerMetadataFactory;

    public function __construct(MetadataFactory $serializerMetadataFactory)
    {
        $this->serializerMetadataFactory = $serializerMetadataFactory;
    }

    public function getParentObjectInlining($object, SerializationContext $context)
    {
        $metadataStack = $context->getMetadataStack();
        $visitingStack = $context->getVisitingStack();

        $parentObject = null;
        if (count($visitingStack) > 0) {
            $parentObject = $visitingStack[0];
        }
        if ($parentObject === $object && count($visitingStack) > 1) {
            $parentObject = $visitingStack[1]; // $object is inlined inside $parentObject
        }

        if (
            (
                $metadataStack->count() > 0 && $metadataStack[0]->inline
                && $this->serializerMetadataFactory->getMetadataForClass(get_class($parentObject)) === $metadataStack[1]
            )
            || (
                $this->isSubclassOf(get_class($object), 'Pagerfanta\Pagerfanta')
                && $this->isSubclassOf(get_class($parentObject), 'FSC\HateoasBundle\Model\HalPagerfanta')
            )
        ) {
            return $parentObject;
        }

        return null;
    }

    public function getXmlRootName($object)
    {
        $classMetadata = $this->serializerMetadataFactory->getMetadataForClass(get_class($object));

        return $classMetadata->xmlRootName;
    }

    private function isSubclassOf($class1, $class2)
    {
        $class = new \ReflectionClass($class1);

        return $class1 === $class2 || $class->isSubclassOf($class2);
    }
}
