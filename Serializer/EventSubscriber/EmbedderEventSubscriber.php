<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\TypeParser;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Pagerfanta\PagerfantaInterface;

use FSC\HateoasBundle\Factory\ContentFactoryInterface;
use FSC\HateoasBundle\Factory\RouteAwarePagerFactoryInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationContentMetadataInterface;

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

    protected $contentFactory;
    protected $serializerMetadataFactory;
    protected $routeAwarePagerFactory;
    protected $typeParser;

    public function __construct(ContentFactoryInterface $contentFactory, MetadataFactoryInterface $serializerMetadataFactory,
        RouteAwarePagerFactoryInterface $routeAwarePagerFactory, TypeParser $typeParser = null)
    {
        $this->contentFactory = $contentFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->routeAwarePagerFactory = $routeAwarePagerFactory;
        $this->typeParser = $typeParser ?: new TypeParser();
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($relationsContent = $this->contentFactory->create($event->getObject())) || empty($relationsContent)) {
            return;
        }

        $visitor = $event->getVisitor(); /** @var $visitor XmlSerializationVisitor */

        foreach ($relationsContent as $rel => $relationContent) {
            $entryNode = $visitor->getDocument()->createElement($this->getRelationXmlElementName($relationContent['metadata'], $relationContent['content']));
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $visitor->getCurrentNode()->setAttribute('rel', $rel);

            if (null !== ($node = $visitor->getNavigator()->accept($this->getRelationContent($event, $relationContent), $relationContent['metadata']->getContent()->getSerializerType(), $visitor))) {
                $visitor->getCurrentNode()->appendChild($node);
            }

            $visitor->revertCurrentNode();
        }
    }

    public function onPostSerialize(Event $event)
    {
        if (null === ($relationsContent = $this->contentFactory->create($event->getObject())) || empty($relationsContent)) {
            return;
        }

        $visitor = $event->getVisitor();

        $relationsData = array();
        foreach ($relationsContent as $rel => $relationContent) {
            $relationsData[$rel] = $visitor->getNavigator()->accept($this->getRelationContent($event, $relationContent), $relationContent['metadata']->getContent()->getSerializerType(), $visitor);
        }

        $event->getVisitor()->addData('relations', $relationsData);
    }

    protected function getRelationXmlElementName(RelationMetadataInterface $relationMetadata, $content)
    {
        $elementName = 'relation';

        if (null !== $relationMetadata->getContent()->getSerializerXmlElementName()) {
            $elementName = $relationMetadata->getContent()->getSerializerXmlElementName();
        } else if (null !== $relationMetadata->getContent()->getSerializerXmlElementRootName()) {
            $classMetadata = $this->serializerMetadataFactory->getMetadataForClass(get_class($content));
            $elementName = $classMetadata->xmlRootName ?: $elementName;
        }

        return $elementName;
    }

    protected function getRelationContent(Event $event, $relation)
    {
        if (!$relation['content'] instanceof PagerfantaInterface) {
            return $relation['content'];
        }

        return $this->routeAwarePagerFactory->create($relation['content'], $relation['metadata'], $event->getObject());
    }
}
