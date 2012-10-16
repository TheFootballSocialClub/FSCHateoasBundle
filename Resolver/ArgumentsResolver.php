<?php

namespace FSC\HateoasBundle\Resolver;

class ArgumentsResolver implements ArgumentsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(\ReflectionFunctionAbstract $method, $parameters)
    {
        $arguments = array();

        foreach ($method->getParameters() as $parameter) {
            $arguments[] = $parameters[$parameter->getName()];
        }

        return $arguments;
    }
}
