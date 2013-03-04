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
     * @var boolean
     */
    public $required = true;

    /**
     * @var \FSC\HateoasBundle\Annotation\Content
     */
    public $embed;
}
