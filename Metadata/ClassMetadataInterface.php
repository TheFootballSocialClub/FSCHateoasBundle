<?php

namespace FSC\HateoasBundle\Metadata;

interface ClassMetadataInterface
{
    /**
     * @return array<RelationMetadataInterface>
     */
    public function getRelations();
}
