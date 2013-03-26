<?php

namespace FSC\HateoasBundle\Model;

/**
 * This is serialized by the serializer.
 */
class Link
{
    private $rel;
    private $href;
    private $attributes;

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
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        if (is_null($attributes)) {
            $attributes = array();
        }
        $this->attributes = $attributes;
    }
}
