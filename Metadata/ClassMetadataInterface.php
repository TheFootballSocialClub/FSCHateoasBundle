<?php

namespace FSC\HateoasBundle\Metadata;

interface ClassMetadataInterface
{
    /**
     * @return array<RelationMetadataInterface>
     */
    public function getRelations();

    /**
     * @param string $rel
     *
     * @return RelationMetadataInterface
     */
    public function getRelation($rel);
}
