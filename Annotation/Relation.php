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
     */
    public $skipIfNull;

    /**
     * @var \FSC\HateoasBundle\Annotation\Content
     */
    public $embed;
}
