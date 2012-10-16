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
            $relations = array();

            foreach ($config['relations'] as $currentRelation) {
                $relation = array(
                    'rel' => $currentRelation['rel'],
                    'route' => $currentRelation['route'],
                    'params' => isset($currentRelation['params']) ? $currentRelation['params'] : array(),
                );

                if (!empty($currentRelation['content'])) {
                    $relation['content'] = array(
                        'provider_id' => $currentRelation['content']['provider_id'],
                        'provider_method' => $currentRelation['content']['provider_method'],
                        'serializer_type' => isset($currentRelation['content']['serializer_type']) ? $currentRelation['content']['serializer_type'] : null,
                    );
                }

                $relations[] = $relation;
            }

            $classMetadata->setRelations($relations);
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
