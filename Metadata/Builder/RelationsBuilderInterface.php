<?php

namespace FSC\HateoasBundle\Metadata\Builder;

interface RelationsBuilderInterface
{
    public function add($rel, $href, array $embed = null, $templated = false);
    public function build();
}
