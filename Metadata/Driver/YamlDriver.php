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
                if (!isset($relation['href'])) {
                    throw new \RuntimeException('The "href" relation parameter is required.');
                }

                $relationMetadata = new RelationMetadata($relation['rel']);

                if (is_array($relation['href'])) {
                    $relationMetadata->setRoute($relation['href']['route']);
                    if (isset($relation['href']['parameters'])) {
                        $relationMetadata->setParams($relation['href']['parameters']);
                    }
                    if (isset($relation['href']['options'])) {
                        $relationMetadata->setOptions($relation['href']['options']);
                    }
                } else {
                    $relationMetadata->setUrl($relation['href']);
                }

                if (!empty($relation['attributes'])) {
                    $relationMetadata->setAttributes($relation['attributes']);
                }

                if (!empty($relation['exclude_if'])) {
                    $relationMetadata->setExcludeIf($relation['exclude_if']);
                }

                if (!empty($relation['content'])) {
                    $relationContent = $relation['content'];

                    if (!empty($relationContent['provider_id']) && !empty($relationContent['property'])) {
                        throw new \RuntimeException('The content configuration can only have either a provider or a property.');
                    }
                    if (!empty($relationContent['provider_id']) && empty($relationContent['provider_method'])) {
                        throw new \RuntimeException('The content configuration needs a "provider_method" when using "provider_id".');
                    }

                    $providerId = $providerMethod = $providerArguments = null;

                    if (!empty($relationContent['provider_id'])) {
                        $providerId     = $relationContent['provider_id'];
                        $providerMethod = $relationContent['provider_method'];

                        $providerArguments = isset($relationContent['provider_arguments']) ? $relationContent['provider_arguments'] : array();
                    } elseif (!empty($relationContent['property'])) {
                        $providerId     = 'fsc_hateoas.factory.identity';
                        $providerMethod = 'get';

                        $providerArguments = array($relationContent['property']);
                    }

                    $relationContentMetadata = new RelationContentMetadata($providerId, $providerMethod);
                    $relationContentMetadata->setProviderArguments($providerArguments);
                    $relationMetadata->setContent($relationContentMetadata);

                    if (isset($relationContent['serializer_type'])) {
                        $relationContentMetadata->setSerializerType($relationContent['serializer_type']);
                    }

                    if (isset($relationContent['serializer_xml_element_name'])) {
                        $relationContentMetadata->setSerializerXmlElementName($relationContent['serializer_xml_element_name']);
                    }

                    if (array_key_exists('serializer_xml_element_name_root_metadata', $relationContent)) {
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
