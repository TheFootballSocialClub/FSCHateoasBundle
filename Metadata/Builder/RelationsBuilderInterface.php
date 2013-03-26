<?php

namespace FSC\HateoasBundle\Metadata\Builder;

interface RelationsBuilderInterface
{
    public function add($rel, $href, array $embed = null, array $attributes = null);
    public function build();
}
