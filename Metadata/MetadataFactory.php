<?php

namespace FSC\HateoasBundle\Metadata;

use Metadata\MetadataFactoryInterface as BaseMetadataFactoryInterface;

use FSC\HateoasBundle\Metadata\ClassMetadata;

class MetadataFactory implements MetadataFactoryInterface
{
    protected $metadataFactory;
    protected $objectsMetadata;

    public function __construct(BaseMetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
        $this->objectsMetadata = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($object)
    {
        if (!is_object($object)) {
            return null;
        }

        $oid = spl_object_hash($object);

        if (isset($this->objectsMetadata[$oid])) {
            return $this->objectsMetadata[$oid];
        }

        return $this->metadataFactory->getMetadataForClass(get_class($object));
    }

    /**
     * {@inheritdoc}
     */
    public function addObjectRelations($object, array $relations)
    {
        $oid = spl_object_hash($object);

        if (!isset($this->objectsMetadata[$oid])) {
            $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object));
            $this->objectsMetadata[$oid] = null !== $classMetadata
                ? clone $classMetadata
                : new ClassMetadata(get_class($object))
            ;
        }

        foreach ($relations as $relation) {
            $this->objectsMetadata[$oid]->addRelation($relation);
        }
    }
}
