<?php

namespace FSC\HateoasBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class FSCHateoasExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        foreach (array('services.yml', 'metadata.yml') as $file) {
            $loader->load($file);
        }

        $container
            ->getDefinition('fsc_hateoas.serializer.handler.pagerfanta')
            ->replaceArgument(3, $config['pagerfanta']['xml_elements_names_use_serializer_metadata'])
        ;

        if ($config['form_handler']) {
            $container
                ->getDefinition('fsc_hateoas.serializer.handler.form')
                ->addTag('jms_serializer.subscribing_handler')
            ;
        }

        $relationsBuilderFactoryDefinition = $container->getDefinition('fsc_hateoas.metadata.relation_builder.factory');
        $relationsBuilderFactoryDefinition->addArgument($config['pagerfanta']['parameters_name']['page']);
        $relationsBuilderFactoryDefinition->addArgument($config['pagerfanta']['parameters_name']['limit']);

        $container->setParameter('fsc_hateoas.json.links_key', $config['json']['links_key']);
        $container->setParameter('fsc_hateoas.json.relations_key', $config['json']['relations_key']);

        foreach (array('fsc_hateoas.factory.link', 'fsc_hateoas.routing.relation_url_generator', 'fsc_hateoas.factory.form_view') as $serviceName) {
            $service = $container->getDefinition($serviceName);
            $service->addArgument($config['absolute_url']);
        }

        $this->configureMetadata($config, $container);
    }

    protected function configureMetadata(array $config, ContainerBuilder $container)
    {
        // The following configuration has been copied from JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension

        if ('none' === $config['metadata']['cache']) {
            $container->removeAlias('fsc_hateoas.metadata.cache');
        } elseif ('file' === $config['metadata']['cache']) {
            $container
                ->getDefinition('fsc_hateoas.metadata.cache.file')
                ->replaceArgument(0, $config['metadata']['file_cache']['dir'])
            ;

            $dir = $container->getParameterBag()->resolveValue($config['metadata']['file_cache']['dir']);
            if (!file_exists($dir) && (!$rs = @mkdir($dir, 0777, true))) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        } else {
            $container->setAlias('fsc_hateoas.metadata.cache', new Alias($config['metadata']['cache'], false));
        }

        $container
            ->getDefinition('fsc_hateoas.metadata.base_factory')
            ->replaceArgument(2, $config['metadata']['debug'])
        ;

        // directories
        $directories = array();
        $bundles = $container->getParameter('kernel.bundles');
        if ($config['metadata']['auto_detection']) {
            foreach ($bundles as $name => $class) {
                $ref = new \ReflectionClass($class);

                $directories[$ref->getNamespaceName()] = dirname($ref->getFileName()).'/Resources/config/hateoas';
            }
        }
        foreach ($config['metadata']['directories'] as $directory) {
            $directory['path'] = rtrim(str_replace('\\', '/', $directory['path']), '/');

            if ('@' === $directory['path'][0]) {
                $bundleName = substr($directory['path'], 1, strpos($directory['path'], '/') - 1);

                if (!isset($bundles[$bundleName])) {
                    throw new \RuntimeException(sprintf('The bundle "%s" has not been registered with AppKernel. Available bundles: %s', $bundleName, implode(', ', array_keys($bundles))));
                }

                $ref = new \ReflectionClass($bundles[$bundleName]);
                $directory['path'] = dirname($ref->getFileName()).substr($directory['path'], strlen('@'.$bundleName));
            }

            $directories[rtrim($directory['namespace_prefix'], '\\')] = rtrim($directory['path'], '\\/');
        }
        $container
            ->getDefinition('fsc_hateoas.metadata.file_locator')
            ->replaceArgument(0, $directories)
        ;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }

    public function getAlias()
    {
        return 'fsc_hateoas';
    }
}
