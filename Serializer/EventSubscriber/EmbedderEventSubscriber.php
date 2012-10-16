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

        foreach ($relationsContent as $rel => $relation) {
            $entryNode = $visitor->getDocument()->createElement($this->getRelationXmlElementName($relation));
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $visitor->getCurrentNode()->setAttribute('rel', $rel);

            if (null !== ($node = $visitor->getNavigator()->accept($this->getRelationContent($event, $relation), $this->getRelationType($relation), $visitor))) {
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
            $relationsData[$rel] = $visitor->getNavigator()->accept($this->getRelationContent($event, $relation), $this->getRelationType($relation), $visitor);
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

    protected function getRelationContent(Event $event, $relation)
    {
        if (!$relation['content']['value'] instanceof PagerfantaInterface) {
            return $relation['content']['value'];
        }

        return $this->routeAwarePagerFactory->create($relation['content']['value'], $relation, $event->getObject());
    }
}
