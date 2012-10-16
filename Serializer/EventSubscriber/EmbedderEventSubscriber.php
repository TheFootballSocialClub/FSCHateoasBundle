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

use FSC\HateoasBundle\Factory\ContentFactoryInterface;
use FSC\HateoasBundle\Factory\PagerLinkFactoryInterface;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;
use FSC\HateoasBundle\Serializer\LinkSerializationHelper;

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
    protected $pagerLinkFactory;
    protected $linkSerializationHelper;
    protected $typeParser;

    public function __construct(ContentFactoryInterface $contentFactory, MetadataFactoryInterface $serializerMetadataFactory,
        PagerLinkFactoryInterface $pagerLinkFactory, LinkSerializationHelper $linkSerializationHelper, TypeParser $typeParser = null)
    {
        $this->contentFactory = $contentFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->pagerLinkFactory = $pagerLinkFactory;
        $this->linkSerializationHelper = $linkSerializationHelper;
        $this->typeParser = $typeParser ?: new TypeParser();
    }

    public function onPostSerializeXML(Event $event)
    {
        if (null === ($relationsContent = $this->contentFactory->create($event->getObject()))) {
            return;
        }

        if (empty($relationsContent)) {
            return;
        }

        $visitor = $event->getVisitor(); /** @var $visitor XmlSerializationVisitor */

        foreach ($relationsContent as $rel => $relation) {
            $elementName = 'relation';
            if (null !== $relation['content']['serializer_xml_element_name']) {
                $elementName = $relation['content']['serializer_xml_element_name'];
            } else if (true === $relation['content']['serializer_xml_element_name_root_metadata']) {
                $classMetadata = $this->serializerMetadataFactory->getMetadataForClass(get_class($relation['content']['value']));
                $elementName = $classMetadata->xmlRootName ?: $elementName;
            }

            $entryNode = $visitor->getDocument()->createElement($elementName);
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $type = null !== $relation['content']['serializer_type'] ? $this->typeParser->parse($relation['content']['serializer_type']) : null;

            $visitor->getCurrentNode()->setAttribute('rel', $rel);

            if ($relation['content']['value'] instanceof \Pagerfanta\Pagerfanta) {
                // Add links

                $links = $this->pagerLinkFactory->createPagerLinks($event->getObject(), $relation['content']['value'], $relation);
                $this->linkSerializationHelper->addLinksToXMLSerialization($links, $visitor);
            }

            $node = $visitor->getNavigator()->accept($relation['content']['value'], $type, $visitor);

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
        if (null === ($relationsContent = $this->contentFactory->create($event->getObject()))) {
            return;
        }

        if (empty($relationsContent)) {
            return;
        }

        $visitor = $event->getVisitor();

        $relationsData = array();
        foreach ($relationsContent as $rel => $relation) {
            $type = null !== $relation['content']['serializer_type'] ? $this->typeParser->parse($relation['content']['serializer_type']) : null;

            $relationData = array();

            if ($relation['content']['value'] instanceof \Pagerfanta\Pagerfanta) {
                // Add links

                $links = $this->pagerLinkFactory->createPagerLinks($event->getObject(), $relation['content']['value'], $relation);
                $relationData['links'] = $this->linkSerializationHelper->createGenericLinksData($links, $visitor);
            }

            $relationData = array_merge($relationData, $visitor->getNavigator()->accept($relation['content']['value'], $type, $visitor));

            $relationsData[$rel] = $relationData;
        }

        $event->getVisitor()->addData('relations', $relationsData);
    }
}
