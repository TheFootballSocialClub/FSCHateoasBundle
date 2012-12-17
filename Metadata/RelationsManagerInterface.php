<?php

namespace FSC\HateoasBundle\Metadata;

interface RelationsManagerInterface
{
    public function addBasicRelations($object, $route = null, $routeParameters = array());
    public function addRelation($object, $rel, $href, array $embed = null);
}
