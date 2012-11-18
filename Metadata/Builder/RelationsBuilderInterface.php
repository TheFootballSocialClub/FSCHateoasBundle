<?php

namespace FSC\HateoasBundle\Metadata\Builder;

interface RelationsBuilderInterface
{
    public function add($rel, array $href, array $embed = null);
    public function build();
}
