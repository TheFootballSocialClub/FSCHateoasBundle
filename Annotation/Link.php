<?php

namespace FSC\HateoasBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Link
{
    /**
     * @Required
     *
     * @var string
     */
    public $rel;

    /**
     * @Required
     *
     * @var string
     */
    public $route;

    /**
     * @var array
     */
    public $params;
}