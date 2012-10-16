<?php

namespace FSC\HateoasBundle\Factory;

use Metadata\MetadataFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use FSC\HateoasBundle\Factory\ParametersFactoryInterface;
use FSC\HateoasBundle\Resolver\ArgumentsResolverInterface;
use FSC\HateoasBundle\Metadata\ClassMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;

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
        foreach ($classMetadata->getRelations() as $relationMetadata) {
            if (null === $relationMetadata->getContent()) {
                continue;
            }

            if (isset($relationsContent[$relationMetadata->getRel()])) {
                throw new \RuntimeException(sprintf('You cannot embed content twice for the same rel "%s".', $relationMetadata['rel']));
            }

            $relationsContent[$relationMetadata->getRel()] = array(
                'metadata' => $relationMetadata,
                'content' => $this->getContent($relationMetadata, $object),
            );
        }

        return $relationsContent;
    }

    protected function getContent(RelationMetadataInterface $relationMetadata, $object)
    {
        $provider = $this->container->get($relationMetadata->getContent()->getProviderId());
        $providerClass = new \ReflectionClass(get_class($provider));
        $providerMethod = $providerClass->getMethod($relationMetadata->getContent()->getProviderMethod());

        $parameters = $this->parametersFactory->createParameters($object, $relationMetadata->getContent()->getProviderParameters());
        $arguments = $this->argumentsResolver->resolve($providerMethod, $parameters);

        return call_user_func_array(array($provider, $relationMetadata->getContent()->getProviderMethod()), $arguments);
    }
}
