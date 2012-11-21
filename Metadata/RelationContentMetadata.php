<?php

namespace FSC\HateoasBundle\Metadata;

class RelationContentMetadata implements RelationContentMetadataInterface
{
    private $providerId;
    private $providerMethod;
    private $providerArguments;
    private $serializerType;
    private $serializerXmlElementName;
    private $serializerXmlElementRootName;

    public function __construct($providerId, $providerMethod)
    {
        $this->providerId = $providerId;
        $this->providerMethod = $providerMethod;

        $this->providerArguments = array();
        $this->serializerXmlElementRootName = true;
    }

    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    public function setProviderMethod($providerMethod)
    {
        $this->providerMethod = $providerMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMethod()
    {
        return $this->providerMethod;
    }

    public function setSerializerType($serializerType)
    {
        $this->serializerType = $serializerType;
    }

    public function setProviderArguments($providerArguments)
    {
        $this->providerArguments = $providerArguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderArguments()
    {
        return $this->providerArguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializerType()
    {
        return $this->serializerType;
    }

    public function setSerializerXmlElementName($serializerXmlElementName)
    {
        $this->serializerXmlElementName = $serializerXmlElementName;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializerXmlElementName()
    {
        return $this->serializerXmlElementName;
    }

    public function setSerializerXmlElementRootName($serializerXmlElementRootName)
    {
        $this->serializerXmlElementRootName = (Boolean) $serializerXmlElementRootName;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializerXmlElementRootName()
    {
        return $this->serializerXmlElementRootName;
    }
}
