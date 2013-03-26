<?php

namespace FSC\HateoasBundle\Model;

/**
 * This is serialized by the serializer.
 */
class Link
{
    private $rel;
    private $href;
    private $relationAttributes;

    public function setHref($href)
    {
        $this->href = $href;
    }

    public function getHref()
    {
        return $this->href;
    }

    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    public function getRel()
    {
        return $this->rel;
    }

    /**
     * @return array
     */
    public function getRelationAttributes()
    {
        return $this->relationAttributes;
    }

    /**
     * @param array $relationAttributes
     */
    public function setRelationAttributes($relationAttributes)
    {
        $this->relationAttributes = $relationAttributes;
    }
}
