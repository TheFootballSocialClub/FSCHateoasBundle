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
use FSC\HateoasBundle\Factory\PagerLinkFactoryInterface;
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
        if (null === ($relationsContent = $this->contentFactory->create($event->getObject())) || empty($relationsContent)) {
            return;
        }

        $visitor = $event->getVisitor(); /** @var $visitor XmlSerializationVisitor */

        foreach ($relationsContent as $rel => $relation) {
            $entryNode = $visitor->getDocument()->createElement($this->getRelationXmlElementName($relation));
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $visitor->getCurrentNode()->setAttribute('rel', $rel);

            if ($relation['content']['value'] instanceof PagerfantaInterface) {
                $links = $this->pagerLinkFactory->createPagerLinks($event->getObject(), $relation['content']['value'], $relation);
                $this->linkSerializationHelper->addLinksToXMLSerialization($links, $visitor);
            }

            if (null !== ($node = $visitor->getNavigator()->accept($relation['content']['value'], $this->getRelationType($relation), $visitor))) {
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
        foreach ($relationsContent as $rel => $relation) {
            $relationData = array();

            if ($relation['content']['value'] instanceof PagerfantaInterface) {
                $links = $this->pagerLinkFactory->createPagerLinks($event->getObject(), $relation['content']['value'], $relation);
                $relationData['links'] = $this->linkSerializationHelper->createGenericLinksData($links, $visitor);
            }

            $relationData = array_merge($relationData, $visitor->getNavigator()->accept($relation['content']['value'], $this->getRelationType($relation), $visitor));

            $relationsData[$rel] = $relationData;
        }

        $event->getVisitor()->addData('relations', $relationsData);
    }

    protected function getRelationType($relation)
    {
        return null !== $relation['content']['serializer_type'] ? $this->typeParser->parse($relation['content']['serializer_type']) : null;
    }

    protected function getRelationXmlElementName($relation)
    {
        $elementName = 'relation';
        if (null !== $relation['content']['serializer_xml_element_name']) {
            $elementName = $relation['content']['serializer_xml_element_name'];
        } else if (true === $relation['content']['serializer_xml_element_name_root_metadata']) {
            $classMetadata = $this->serializerMetadataFactory->getMetadataForClass(get_class($relation['content']['value']));
            $elementName = $classMetadata->xmlRootName ?: $elementName;
        }

        return $elementName;
    }
}
