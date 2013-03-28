<?php

namespace FSC\HateoasBundle\Metadata\Builder;

interface RelationsBuilderInterface
{
    public function add($rel, $href, array $embed = null, array $attributes = null, array $excludeIf = null);
    public function build();
}
