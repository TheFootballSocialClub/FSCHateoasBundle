<?php

namespace FSC\HateoasBundle\Metadata;

use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

class ClassMetadata extends MergeableClassMetadata implements ClassMetadataInterface
{
    /**
     * @var array<RelationMetadataInterface>
     */
    protected $relations = array();

    /**
     * {@inheritdoc}
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * {@inheritdoc}
     */
    public function addRelation(RelationMetadataInterface $relation)
    {
        $this->relations[] = $relation;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelation($rel)
    {
        foreach ($this->relations as $relationMetadata) {
            if ($relationMetadata->getRel() === $rel) {
                return $relationMetadata;
            }
        }
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

    public function merge(MergeableInterface $object)
    {
        if (!$object instanceof ClassMetadata) {
            throw new \InvalidArgumentException('object must be an instance of FSC\HateoasBundle\Metadata\ClassMetadata.');
        }
        parent::merge($object);

        $this->relations = array_merge($this->relations, $object->getRelations());
    }
}
