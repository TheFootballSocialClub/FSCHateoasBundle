<?php

namespace FSC\HateoasBundle\Resolver;

class ArgumentsResolver implements ArgumentsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(\ReflectionFunctionAbstract $method, array $parameters)
    {
        $arguments = array();

        foreach ($method->getParameters() as $parameter) {
            if (!array_key_exists($name = $parameter->getName(), $parameters)) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            $arguments[] = $parameters[$name];
        }

        return $arguments;
    }
}
