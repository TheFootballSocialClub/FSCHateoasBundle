<?php

namespace FSC\HateoasBundle\Metadata;

use FSC\HateoasBundle\Metadata\ClassMetadataInterface;

interface MetadataFactoryInterface
{
    /**
     * @param object $object
     *
     * @return ClassMetadataInterface
     */
    public function getMetadata($object);

    /**
     * @param object $object
     * @param array $relations
     */
    public function addObjectRelations($object, array $relations);
}
