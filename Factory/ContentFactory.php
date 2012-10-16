<?php

namespace FSC\HateoasBundle\Factory;

use Metadata\MetadataFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use FSC\HateoasBundle\Factory\ParametersFactoryInterface;
use FSC\HateoasBundle\Resolver\ArgumentsResolverInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;

class ContentFactory implements ContentFactoryInterface
{
    protected $metadataFactory;
    protected $parametersFactory;
    protected $argumentsResolver;
    protected $container;

    public function __construct(MetadataFactoryInterface $metadataFactory, ParametersFactoryInterface $parametersFactory,
                                ArgumentsResolverInterface $argumentsResolver, ContainerInterface $container)
    {
        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
        $this->argumentsResolver = $argumentsResolver;
        $this->container = $container;
    }

    public function create($object)
    {
        $classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object));
        if (null === $classMetadata) {
            return;
        }

        return $this->createRelationsContent($classMetadata, $object);
    }

    public function createRelationsContent(ClassMetadataInterface $classMetadata, $object)
    {
        $relationsContent = array();
        foreach ($classMetadata->getRelations() as $relation) {
            if (!isset($relation['content'])) {
                continue;
            }

            $content = $this->getContent($relation, $object);

            if (isset($relationsContent[$relation['rel']])) {
                throw new \RuntimeException(sprintf('You cannot embed content twice for the same rel "%s".', $relation['rel']));
            }
            $relationsContent[$relation['rel']] = array(
                'content' => $content,
                'type' => $relation['content']['serializer_type'],
            );
        }

        return $relationsContent;
    }

    protected function getContent(array $relation, $object)
    {
        $provider = $this->container->get($relation['content']['provider_id']);
        $providerClass = new \ReflectionClass(get_class($provider));
        $providerMethod = $providerClass->getMethod($relation['content']['provider_method']);

        $parameters = $this->parametersFactory->createParameters($object, $relation['params']);
        $arguments = $this->argumentsResolver->resolve($providerMethod, $parameters);

        return call_user_func_array(array($provider, $relation['content']['provider_method']), $arguments);
    }
}
