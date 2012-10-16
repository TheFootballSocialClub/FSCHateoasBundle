<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\TypeParser;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use Symfony\Component\Form\Util\PropertyPath;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use FSC\HateoasBundle\Metadata\ClassMetadataInterface;

class EmbedderEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'event' => Events::POST_SERIALIZE,
                'format' => $format,
                'method' => 'onPostSerialize'.('xml' == $format ? 'xml' : ''),
            );
        }

        return $methods;
    }

    /**
     * @var array 'type' => ('name' => '', 'params' => array(...))
     */
    protected static $serializerTypeCache;

    protected $metadataFactory;
    protected $serializerMetadataFactory;
    protected $container;
    protected $typeParser;

    public function __construct(MetadataFactoryInterface $metadataFactory, MetadataFactoryInterface $serializerMetadataFactory,
                                ContainerInterface $container, TypeParser $typeParser = null)
    {
        $this->metadataFactory = $metadataFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->container = $container;
        $this->typeParser = $typeParser ?: new TypeParser();
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($classMetadata = $this->metadataFactory->getMetadataForClass(get_class($event->getObject())))) {
            return;
        }

        $relations = $this->getRelationsContent($classMetadata, $event->getObject());
        if (empty($relations)) {
            return;
        }

        $visitor = $event->getVisitor(); /** @var $visitor XmlSerializationVisitor */

        foreach ($relations as $rel => $relation) {
            $type = (null !== $relation['type']) ? $this->getSerializerType($relation['type']) : $relation['type'];
            if (null === $type) {
                $entryNode = $visitor->getDocument()->createElement('relation');
                $visitor->getCurrentNode()->appendChild($entryNode);
                $visitor->setCurrentNode($entryNode);
            }

            $visitor->getCurrentNode()->setAttribute('rel', $rel);

            $node = $visitor->getNavigator()->accept($relation['content'], $type, $visitor);

            if (null === $type) {
                if (null !== $node) {
                    $visitor->getCurrentNode()->appendChild($node);
                }

                $visitor->revertCurrentNode();
            }
        }
    }

    public function onPostSerialize(Event $event)
    {
        if (null === ($classMetadata = $this->metadataFactory->getMetadataForClass(get_class($event->getObject())))) {
            return;
        }

        $relations = $this->getRelationsContent($classMetadata, $event->getObject());
        if (empty($relations)) {
            return;
        }

        $visitor = $event->getVisitor();

        $relationsData = array();
        foreach ($relations as $rel => $relation) {
            $type = (null !== $relation['type']) ? $this->typeParser->parse($relation['type']) : $relation['type'];
            $relationsData[$rel] = $visitor->getNavigator()->accept($relation['content'], $type, $visitor);
        }

        $event->getVisitor()->addData('relations', $relationsData);
    }

    public static function resolveMethodArguments(\ReflectionMethod $method, $parameters)
    {
        $arguments = array();

        foreach ($method->getParameters() as $parameter) {
            $arguments[] = $parameters[$parameter->getName()];
        }

        return $arguments;
    }

    public static function createParameters($parameters, $object)
    {
        array_walk($parameters, function (&$value, $key) use ($object) {
            $propertyPath = new PropertyPath($value);
            $value = $propertyPath->getValue($object);
        });

        return $parameters;
    }

    protected function getRelationsContent(ClassMetadataInterface $classMetadata, $object)
    {
        $relationsContent = array();
        foreach ($classMetadata->getRelations() as $relation) {
            if (!isset($relation['content_provider'])) {
                continue;
            }

            $provider = $this->container->get($relation['content_provider']['id']);
            $providerClass = new \ReflectionClass(get_class($provider));
            $providerMethod = $providerClass->getMethod($relation['content_provider']['method']);

            $parameters = $this->createParameters($relation['params'], $object);
            $arguments = $this->resolveMethodArguments($providerMethod, $parameters);
            $content = call_user_func_array(array($provider, $relation['content_provider']['method']), $arguments);

            if (isset($relationsContent[$relation['rel']])) {
                throw new \RuntimeException(sprintf('You cannot embed content twice for the same rel "%s".', $relation['rel']));
            }
            $relationsContent[$relation['rel']] = array(
                'content' => $content,
                'type' => null,
            );
        }

        return $relationsContent;
    }
}
