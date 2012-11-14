<?php

namespace FSC\HateoasBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class Content
{
    /**
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

    /**
     * @var string
     */
    public $property;
}
