<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\SerializerBundle\Serializer\TypeParser;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Serializer\EventDispatcher\Events;
use JMS\SerializerBundle\Serializer\EventDispatcher\Event;
use Metadata\MetadataFactoryInterface;

use FSC\HateoasBundle\Factory\ContentFactoryInterface;
use FSC\HateoasBundle\Factory\LinksAwareWrapperFactoryInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationContentMetadataInterface;
use FSC\HateoasBundle\Factory\ParametersFactoryInterface;

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
    protected $linksAwareWrapperFactory;
    protected $parametersFactory;
    protected $typeParser;
    protected $embeddedCollectionName;

    public function __construct(
        ContentFactoryInterface $contentFactory,
        MetadataFactoryInterface $serializerMetadataFactory,
        LinksAwareWrapperFactoryInterface $linksAwareWrapperFactory,
        ParametersFactoryInterface $parametersFactory,
        TypeParser $typeParser = null,
        array $jsonOptions
    ) {
        $this->contentFactory = $contentFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->linksAwareWrapperFactory = $linksAwareWrapperFactory;
        $this->parametersFactory = $parametersFactory;
        $this->typeParser = $typeParser ?: new TypeParser();
        $this->embeddedCollectionName = $jsonOptions['relations'];
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($relationsContent = $this->contentFactory->create($event->getObject())) || empty($relationsContent)) {
            return;
        }

        $visitor = $event->getVisitor(); /** @var $visitor XmlSerializationVisitor */

        foreach ($relationsContent as $relationMetadata) {
            $relationContent = $relationsContent[$relationMetadata];

            $entryNode = $visitor->getDocument()->createElement($this->getRelationXmlElementName($relationMetadata, $relationContent));
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $visitor->getCurrentNode()->setAttribute('rel', $relationMetadata->getRel());

            $node = $visitor->getNavigator()->accept(
                $this->getRelationContent($event, $relationContent, $relationMetadata),
                $this->getContentType($relationMetadata->getContent()),
                $visitor
            );
            if (null !== $node) {
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
        foreach ($relationsContent as $relationMetadata) {
            $relationContent = $relationsContent[$relationMetadata];
            $relationsData[$relationMetadata->getRel()] = $visitor->getNavigator()->accept(
                $this->getRelationContent($event, $relationContent, $relationMetadata),
                $this->getContentType($relationMetadata->getContent()),
                $visitor
            );
        }

        $event->getVisitor()->addData($this->embeddedCollectionName, $relationsData);
    }

    protected function getRelationXmlElementName(RelationMetadataInterface $relationMetadata, $content)
    {
        $elementName = 'relation';

        if (null !== $relationMetadata->getContent()->getSerializerXmlElementName()) {
            $elementName = $relationMetadata->getContent()->getSerializerXmlElementName();
        } elseif (null !== $relationMetadata->getContent()->getSerializerXmlElementRootName()) {
            $classMetadata = $this->serializerMetadataFactory->getMetadataForClass(get_class($content));
            $elementName = $classMetadata->xmlRootName ?: $elementName;
        }

        return $elementName;
    }

    protected function getRelationContent(Event $event, $content, RelationMetadataInterface $relationMetadata)
    {
        $parameters = $this->parametersFactory->createParameters($event->getObject(), $relationMetadata->getParams());

        return $this->linksAwareWrapperFactory->create($content, $relationMetadata->getRoute(), $parameters) ?: $content;
    }

    protected function getContentType(RelationContentMetadataInterface $relationContentMetadata)
    {
        if (null === $relationContentMetadata->getSerializerType()) {
            return null;
        }

        return $this->typeParser->parse($relationContentMetadata->getSerializerType());
    }
}
