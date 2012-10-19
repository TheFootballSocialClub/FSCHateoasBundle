<?php

namespace FSC\HateoasBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class Content
{
    /**
     * @Required
     *
     * @var array<string>
     */
    public $provider;

    /**
     * @var array
     */
    public $providerArguments;

    /**
     * @var string
     */
    public $serializerType;

    /**
     * @var string
     */
    public $serializerXmlElementName;

    /**
     * @var boolean
     */
    public $serializerXmlElementNameRootMetadata;
}
