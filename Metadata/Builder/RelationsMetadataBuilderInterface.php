<?php

namespace FSC\HateoasBundle\Metadata\Builder;

interface RelationsMetadataBuilderInterface
{
    public function add($rel, array $href, array $embed = null);
    public function build();
}
