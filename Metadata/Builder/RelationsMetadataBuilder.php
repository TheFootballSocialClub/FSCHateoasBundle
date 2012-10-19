<?php

namespace FSC\HateoasBundle\Metadata\Builder;

use FSC\HateoasBundle\Metadata\RelationMetadata;
use FSC\HateoasBundle\Metadata\RelationContentMetadata;

class RelationsMetadataBuilder implements RelationsMetadataBuilderInterface
{
    /**
     * @var array<RelationMetadataInterface>
     */
    protected $relationsMetadata;

    public function __construct()
    {
        $this->relationsMetadata = array();
    }

    public function add($rel, array $href, array $embed = null)
    {
        if (!isset($href['route'])) {
            throw new \RuntimeException('href\'s "route" is required.');
        }

        $relationMetadata = new RelationMetadata($rel, $href['route']);

        if (isset($href['parameters'])) {
            $relationMetadata->setParams($href['parameters']);
        }

        if (null !== $embed) {
            if (!isset($embed['provider']) || 2 !== count($embed['provider'])) {
                throw new \RuntimeException('content "provider" is required, and should be an array of 2 values. [service, method]');
            }

            $contentMetadata = new RelationContentMetadata($embed['provider'][0], $embed['provider'][1]);

            if (isset($embed['providerArguments'])) {
                $contentMetadata->setProviderArguments($embed['providerArguments']);
            }

            if (isset($embed['serializerType'])) {
                $contentMetadata->setSerializerType($embed['serializerType']);
            }

            if (isset($embed['serializerXmlElementName'])) {
                $contentMetadata->setSerializerXmlElementName($embed['serializerXmlElementName']);
            }

            if (isset($embed['serializerXmlElementRootMetadata'])) {
                $contentMetadata->setSerializerXmlElementRootName($embed['serializerXmlElementRootMetadata']);
            }

            $relationMetadata->setContent($contentMetadata);
        }

        $this->relationsMetadata[] = $relationMetadata;
    }

    public function build()
    {
        return $this->relationsMetadata;
    }
}
