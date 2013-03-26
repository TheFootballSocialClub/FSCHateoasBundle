<?php

namespace FSC\HateoasBundle\Metadata\Builder;

interface RelationsBuilderInterface
{
    public function add($rel, $href, array $embed = null, array $relationAttributes = null);
    public function build();
}
