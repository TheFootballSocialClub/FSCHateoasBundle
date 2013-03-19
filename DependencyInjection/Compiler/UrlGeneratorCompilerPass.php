<?php

namespace FSC\HateoasBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class UrlGeneratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fsc_hateoas.routing.relation_url_generator')) {
            return;
        }

        $definition = $container->getDefinition(
            'fsc_hateoas.routing.relation_url_generator'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'fsc_hateoas.url_generator'
        );

        foreach ($taggedServices as $id => $attributes) {
            $alias = !empty($attributes[0]['alias']) ? $attributes[0]['alias'] : $id;
            $definition->addMethodCall(
                'setUrlGenerator',
                array($alias, new Reference($id))
            );
        }
    }
}