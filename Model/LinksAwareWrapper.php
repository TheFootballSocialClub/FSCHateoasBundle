<?php

namespace FSC\HateoasBundle\Model;

/**
 * This class is used to hold links for data that can't have relations (links) metadata.
 *
 * The LinksAwareWrapperHandler handles this class during serialization.
 */
class LinksAwareWrapper
{
    protected $data;
    protected $links;

    public function __construct($data, array $links)
    {
        $this->data = $data;
        $this->links = $links;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getLinks()
    {
        return $this->links;
    }
}
