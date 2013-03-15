<?php

namespace FSC\HateoasBundle\Annotation;

/**
 * @Annotation
 * @Target("ANNOTATION")
 */
final class Route
{
    /**
     * @Required
     *
     * @var string
     */
    public $value;

    /**
     * @var array
     */
    public $parameters;

    /**
     * @var array
     */
    public $options;
}
