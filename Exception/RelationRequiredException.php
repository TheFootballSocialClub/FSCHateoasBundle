<?php

namespace FSC\HateoasBundle\Exception;

use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

class RelationRequiredException extends \Exception {

    public function __construct(RelationMetadataInterface $relation, $object){
        $className = get_class($object);
        $rel = $relation->getRel();

        return parent::__construct(sprintf('Relation "%s" in "$object" is required', $rel, $className));
    }

}