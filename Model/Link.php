<?php

namespace FSC\HateoasBundle\Model;

/**
 * This is serialized by the serializer.
 */
class Link
{
    private $rel;
    private $href;
    private $templated;

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
     * @return boolean
     */
    public function getTemplated()
    {
        return $this->templated;
    }

    /**
     * @param boolean $templated
     */
    public function setTemplated($templated)
    {
        $this->templated = (bool) $templated;
    }
}
