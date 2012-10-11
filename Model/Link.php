<?php

namespace FSC\HateoasBundle\Model;

use JMS\SerializerBundle\Annotation as Serializer;

class Link
{
    /**
     * @Serializer\XmlAttribute
     */
    private $rel;

    /**
     * @Serializer\XmlAttribute
     */
    private $href;

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
}
