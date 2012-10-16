<?php

namespace FSC\HateoasBundle\Metadata;

class RelationMetadata implements RelationMetadataInterface
{
    private $rel;
    private $route;
    private $params;
    private $content;

    public function __construct($rel, $route)
    {
        $this->rel = $rel;
        $this->route = $route;
        $this->params = array();
    }

    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->params;
    }

    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    /**
     * {@inheritdoc}
     */
    public function getRel()
    {
        return $this->rel;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return $this->route;
    }

    public function setContent(RelationContentMetadataInterface $content)
    {
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }
}
