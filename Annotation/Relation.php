<?php

namespace FSC\HateoasBundle\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Relation
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

    /**
     * @var array
     */
    public $content;
}
