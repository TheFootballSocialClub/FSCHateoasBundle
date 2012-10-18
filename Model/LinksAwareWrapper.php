<?php

namespace FSC\HateoasBundle\Model;

/**
 * This class is used for data that can't have relations (links) metadata
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
