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
     * @var \FSC\HateoasBundle\Annotation\Route
     */
    public $href;

    /**
     * @var \FSC\HateoasBundle\Annotation\Content
     */
    public $embed;
}
