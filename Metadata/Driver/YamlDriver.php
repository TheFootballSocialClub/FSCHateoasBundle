<?php

namespace FSC\HateoasBundle\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml;

use FSC\HateoasBundle\Metadata\ClassMetadata;
use FSC\HateoasBundle\Metadata\RelationMetadata;
use FSC\HateoasBundle\Metadata\RelationContentMetadata;

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
            foreach ($config['relations'] as $relation) {
                $relationMetadata = new RelationMetadata($relation['rel'], $relation['route']);
                if (isset($relation['parameters'])) {
                    $relationMetadata->setParams($relation['parameters']);
                }

                if (!empty($relation['content'])) {
                    $relationContent = $relation['content'];
                    $relationContentMetadata = new RelationContentMetadata($relationContent['provider_id'], $relationContent['provider_method']);
                    $relationMetadata->setContent($relationContentMetadata);

                    if (isset($relationContent['provider_parameters'])) {
                        $relationContentMetadata->setProviderParameters($relationContent['provider_parameters']);
                    }

                    if (isset($relationContent['serializer_type'])) {
                        $relationContentMetadata->setSerializerType($relationContent['serializer_type']);
                    }

                    if (isset($relationContent['serializer_xml_element_name'])) {
                        $relationContentMetadata->setSerializerXmlElementName($relationContent['serializer_xml_element_name']);
                    }

                    if (isset($relationContent['serializer_xml_element_name_root_metadata'])) {
                        $relationContentMetadata->setSerializerXmlElementRootName($relationContent['serializer_xml_element_name_root_metadata']);
                    }
                }

                $classMetadata->addRelation($relationMetadata);
            }
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
