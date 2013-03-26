<?php

namespace FSC\HateoasBundle\Metadata;

interface RelationMetadataInterface
{
    /**
     * @return string
     */
    public function getRel();

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

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return array|null
     */
    public function getAttributes();
}
