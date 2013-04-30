<?php

namespace FSC\HateoasBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;

use FSC\HateoasBundle\Factory\ParametersFactoryInterface;
use FSC\HateoasBundle\Metadata\MetadataFactoryInterface;
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
        $classMetadata = $this->metadataFactory->getMetadata($object);
        if (null === $classMetadata) {
            return;
        }

        return $this->createRelationsContent($classMetadata, $object);
    }

    public function createRelationsContent(ClassMetadataInterface $classMetadata, $object)
    {
        $relationsContent = new \SplObjectStorage();

        /**
         * @var RelationMetadataInterface $relationMetadata
         */
        foreach ($classMetadata->getRelations() as $relationMetadata) {
            if (null === $relationMetadata->getContent()) {
                continue;
            }

            if (!$this->parametersFactory->createExclude($object, $relationMetadata->getExcludeIf())
                && $content = $this->getContent($relationMetadata, $object)
            ) {
                $relationsContent->attach($relationMetadata, $content);
            }
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
