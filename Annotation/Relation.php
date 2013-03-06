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
     */
    public $href;

    /**
     * @var string|array
     */
    public $skipIfNull = array();

    /**
     * @var \FSC\HateoasBundle\Annotation\Content
     */
    public $embed;

    /**
     * @var array
     */
    public $attributes;
}
