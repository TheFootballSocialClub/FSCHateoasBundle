<?php

namespace FSC\HateoasBundle\Factory;

interface LinksAwareWrapperFactoryInterface
{
    public function create($data, $route = null, $routeParameters = array());
}
