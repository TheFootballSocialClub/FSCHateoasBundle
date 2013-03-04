<?php

namespace FSC\HateoasBundle\Metadata;

interface RelationMetadataInterface
{
    /**
     * @return string
     */
    public function getRel();

    /**
     * @return boolean
     */
    public function getRequired();

    /**
     * @return string|null
     */
    public function getUrl();

    /**
     * @return string
     */
    public function getRoute();

    /**
     * @return array
     */
    public function getParams();

    /**
     * @return null|RelationContentMetadataInterface
     */
    public function getContent();
}
