<?php

namespace FSC\HateoasBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();

        $root = $tb
            ->root('fsc_hateoas', 'array')
                ->children()
        ;

        $root
            ->arrayNode('pagerfanta')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('xml_elements_names_use_serializer_metadata')->defaultTrue()->end()
                ->end()
            ->end()
            ->booleanNode('form_handler')->defaultValue(false)->end()
            ->arrayNode('json')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('links')->defaultValue('links')->end()
                    ->scalarNode('relations')->defaultValue('relations')->end()
                ->end()
            ->end()
        ;

        $this->addMetadataSection($root);

        return $tb;
    }

    /**
     * Copied from JMS\SerializerBundle\DependencyInjection\Configuration::addMetadataSection
     */
    private function addMetadataSection(NodeBuilder $builder)
    {
        $builder
            ->arrayNode('metadata')
                ->addDefaultsIfNotSet()
                ->fixXmlConfig('directory', 'directories')
                ->children()
                    ->scalarNode('cache')->defaultValue('file')->end()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                    ->arrayNode('file_cache')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->defaultValue('%kernel.cache_dir%/fsc_hateoas')->end()
                        ->end()
                    ->end()
                    ->booleanNode('auto_detection')->defaultTrue()->end()
                    ->arrayNode('directories')
                        ->prototype('array')
                        ->children()
                            ->scalarNode('path')->isRequired()->end()
                            ->scalarNode('namespace_prefix')->defaultValue('')->end()
                        ->end()
                    ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
