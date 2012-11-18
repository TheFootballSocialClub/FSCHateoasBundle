<?php

namespace FSC\HateoasBundle\Metadata;

use FSC\HateoasBundle\Metadata\ClassMetadataInterface;

interface MetadataFactoryInterface
{
    public function getMetadata($object);
    public function addObjectRelations($object, array $relations);
}
