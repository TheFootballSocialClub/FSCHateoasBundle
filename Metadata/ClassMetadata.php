<?php

namespace FSC\HateoasBundle\Metadata;

use Metadata\MergeableClassMetadata;

class ClassMetadata extends MergeableClassMetadata implements ClassMetadataInterface
{
    /**
     * @var array<RelationMetadataInterface>
     */
    protected $relations = array();

    public function getRelations()
    {
        return $this->relations;
    }

    public function addRelation(RelationMetadataInterface $relation)
    {
        $this->relations[] = $relation;
    }

    public function serialize()
    {
        return serialize(array(
            $this->relations,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->relations,
            $parentStr
        ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
