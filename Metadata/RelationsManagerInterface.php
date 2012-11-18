<?php

namespace FSC\HateoasBundle\Metadata;

interface RelationsManagerInterface
{
    public function addBasicRelations($object, $route = null, $routeParameters = array());
}
