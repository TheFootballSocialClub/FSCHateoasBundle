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
     * @var \FSC\HateoasBundle\Annotation\Content
     */
    public $embed;

    /**
     * @var boolean
     */
    public $templated;
}
