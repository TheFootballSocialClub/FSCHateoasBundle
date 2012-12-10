<?php

namespace FSC\HateoasBundle\Metadata;

class RelationMetadata implements RelationMetadataInterface
{
    private $rel;
    private $url;
    private $route;
    private $params;
    private $content;
    private $required = true;

    public function __construct($rel)
    {
        $this->rel = $rel;
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

    public function setUrl($url)
    {
        $this->url = (string) $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param Boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return Boolean
     */
    public function isRequired()
    {
        return $this->required;
    }
}
