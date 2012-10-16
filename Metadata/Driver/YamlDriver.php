<?php

namespace FSC\HateoasBundle\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml;

use FSC\HateoasBundle\Metadata\ClassMetadata;

class YamlDriver extends AbstractFileDriver
{
    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $config = Yaml::parse(file_get_contents($file));

        if (!isset($config[$name = $class->getName()])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $name, $file));
        }

        $config = $config[$name];

        $classMetadata = new ClassMetadata($name);

        if (isset($config['relations'])) {
            $links = array();

            foreach ($config['relations'] as $link) {
                $links[] = array(
                    'rel' => $link['rel'],
                    'route' => $link['route'],
                    'params' => isset($link['params']) ? $link['params'] : array(),
                );
            }

            $classMetadata->setLinks($links);
        }

        return $classMetadata;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }
}
