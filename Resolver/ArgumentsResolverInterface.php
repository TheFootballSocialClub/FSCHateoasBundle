<?php

namespace FSC\HateoasBundle\Resolver;

interface ArgumentsResolverInterface
{
    public function resolve(\ReflectionFunctionAbstract $method, array $parameters);
}
