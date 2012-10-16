<?php

namespace FSC\HateoasBundle\Metadata;

interface RelationContentMetadataInterface
{
    /**
     * @return string
     */
    public function getProviderId();

    /**
     * @return string
     */
    public function getProviderMethod();

    /**
     * @return string
     */
    public function getProviderParameters();

    /**
     * @return string
     */
    public function getSerializerType();

    /**
     * @return string
     */
    public function getSerializerXmlElementName();

    /**
     * @return string
     */
    public function getSerializerXmlElementRootName();
}
