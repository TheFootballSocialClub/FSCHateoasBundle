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
                } else if (is_string($relation['href'])) {
                    $relation['href'] = array(
                        'route' => $relation['href'],
                    );
                }

                $relationMetadata = new RelationMetadata($relation['rel'], $relation['href']['route']);
                if (isset($relation['href']['parameters'])) {
                    $relationMetadata->setParams($relation['href']['parameters']);
                }

                if (!empty($relation['content'])) {
                    $relationContent = $relation['content'];

                    if (!empty($relationContent['provider_id']) && !empty($relationContent['property'])) {
                        throw new \RuntimeException("The content configuration can only have either a provider or a property.");
                    }

                    if (!empty($relationContent['provider_id']) && !empty($relationContent['provider_method'])) {
                        $providerId     = $relationContent['provider_id'];
                        $providerMethod = $relationContent['provider_method'];
                    } elseif (!empty($relationContent['property'])) {
                        $providerId     = "fsc_hateoas.factory.property";
                        $providerMethod = "retrieveProperty";
                    } else {
                        throw new \RuntimeException("The content configuration needs either a provider or a property.");
                    }

                    $relationContentMetadata = new RelationContentMetadata($providerId, $providerMethod);
                    $relationMetadata->setContent($relationContentMetadata);

                    if (isset($relationContent['provider_arguments'])) {
                        $relationContentMetadata->setProviderArguments($relationContent['provider_arguments']);
                    }

                    if (isset($relationContent['property'])) {
                        $relationContentMetadata->setProviderArguments(array($relationContent['property']));
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
