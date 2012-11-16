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
    protected $container;

    public function __construct(MetadataFactoryInterface $metadataFactory, ParametersFactoryInterface $parametersFactory,
                                ContainerInterface $container)
    {
        $this->metadataFactory = $metadataFactory;
        $this->parametersFactory = $parametersFactory;
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
        $relationsContent = new \SplObjectStorage();

        foreach ($classMetadata->getRelations() as $relationMetadata) {
            if (null === $relationMetadata->getContent()) {
                continue;
            }

            $relationsContent->attach($relationMetadata, $this->getContent($relationMetadata, $object));
        }

        return $relationsContent->count() === 0 ? null : $relationsContent;
    }

    protected function getContent(RelationMetadataInterface $relationMetadata, $object)
    {
        $provider = $this->container->get($relationMetadata->getContent()->getProviderId());
        $arguments = $this->parametersFactory->createParameters($object, $relationMetadata->getContent()->getProviderArguments());

        return call_user_func_array(array($provider, $relationMetadata->getContent()->getProviderMethod()), $arguments);
    }
}
