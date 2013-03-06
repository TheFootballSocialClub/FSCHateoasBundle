<?php

namespace FSC\HateoasBundle\Metadata\Driver;

use Metadata\Driver\DriverInterface;
use Doctrine\Common\Annotations\Reader;

use FSC\HateoasBundle\Annotation;
use FSC\HateoasBundle\Metadata\ClassMetadata;
use FSC\HateoasBundle\Metadata\RelationMetadata;
use FSC\HateoasBundle\Metadata\RelationContentMetadata;

class AnnotationDriver implements DriverInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $annotations = $this->reader->getClassAnnotations($class);

        if (0 == count($annotations)) {
            return null;
        }

        $classMetadata = new ClassMetadata($name = $class->getName());
        $classMetadata->fileResources[] = $class->getFilename();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Relation) {
                $relationMetadata = new RelationMetadata($annotation->rel);

                if (null !== $annotation->skipIfNull) {
                    $relationMetadata->setSkipIfNull($annotation->skipIfNull);
                }

                if ($annotation->href instanceof Annotation\Route) {
                    $relationMetadata->setRoute($annotation->href->value);
                    if (!empty($annotation->href->parameters)) {
                        $relationMetadata->setParams($annotation->href->parameters);
                    }
                    if (!empty($annotation->href->options)) {
                        $relationMetadata->setOptions($annotation->href->options);
                    }
                } else {
                    $relationMetadata->setUrl($annotation->href);
                }

                if (null !== $annotation->attributes) {
                    $relationMetadata->setAttributes($annotation->attributes);
                }

                if (null !== $annotation->embed && $annotation->embed instanceof Annotation\Content) {
                    if (!(empty($annotation->embed->provider) xor empty($annotation->embed->property))) {
                        throw new \RuntimeException('The @Content annotation needs either a provider or a property.');
                    }

                    $providerId = $providerMethod = $providerArguments = null;

                    if (!empty($annotation->embed->provider)) {
                        if (2 !== count($annotation->embed->provider)) {
                            throw new \RuntimeException('The @Content provider parameters should be an array of 2 values, a service id and a method.');
                        }

                        $providerId = $annotation->embed->provider[0];
                        $providerMethod = $annotation->embed->provider[1];
                        $providerArguments = $annotation->embed->providerArguments ?: array();
                    } else {
                        $providerId = 'fsc_hateoas.factory.identity';
                        $providerMethod = 'get';
                        $providerArguments = array($annotation->embed->property);
                    }

                    $relationContentMetadata = new RelationContentMetadata($providerId, $providerMethod);
                    $relationContentMetadata->setProviderArguments($providerArguments);
                    $relationMetadata->setContent($relationContentMetadata);

                    $relationContentMetadata->setSerializerType($annotation->embed->serializerType);
                    $relationContentMetadata->setSerializerXmlElementName($annotation->embed->serializerXmlElementName);

                    if (null !== $annotation->embed->serializerXmlElementNameRootMetadata) {
                        $relationContentMetadata->setSerializerXmlElementRootName($annotation->embed->serializerXmlElementNameRootMetadata);
                    }
                }

                $classMetadata->addRelation($relationMetadata);
            }
        }

        return $classMetadata;
    }
}
