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
    protected $typeParser;

    public function __construct(ContentFactoryInterface $contentFactory, MetadataFactoryInterface $serializerMetadataFactory,
                                TypeParser $typeParser = null)
    {
        $this->contentFactory = $contentFactory;
        $this->serializerMetadataFactory = $serializerMetadataFactory;
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
            if (null === $relation['type']) {
                $entryNode = $visitor->getDocument()->createElement('relation');
                $visitor->getCurrentNode()->appendChild($entryNode);
                $visitor->setCurrentNode($entryNode);
            }

            $visitor->getCurrentNode()->setAttribute('rel', $rel);

            $node = $visitor->getNavigator()->accept($relation['content'], $relation['type'], $visitor);

            if (null === $relation['type']) {
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
            $relationsData[$rel] = $visitor->getNavigator()->accept($relation['content'], $relation['type'], $visitor);
        }

        $event->getVisitor()->addData('relations', $relationsData);
    }
}
