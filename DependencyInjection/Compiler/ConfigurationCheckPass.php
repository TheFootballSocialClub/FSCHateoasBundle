<?php

namespace FSC\HateoasBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Optimizes the configuration if the Symfony 2.2 property_accessor service is available
 */
class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('property_accessor')) {
            $container
                ->getDefinition('fsc_hateoas.factory.parameters')
                ->replaceArgument(0, $container->getDefinition('property_accessor'))
            ;
        }
    }
}