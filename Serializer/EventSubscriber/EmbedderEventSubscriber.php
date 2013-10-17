<?php

namespace FSC\HateoasBundle\Serializer\EventSubscriber;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\TypeParser;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\Event;

use Symfony\Component\Form\FormView;
use Pagerfanta\PagerfantaInterface;

use FSC\HateoasBundle\Factory\ContentFactoryInterface;
use FSC\HateoasBundle\Factory\ParametersFactoryInterface;
use FSC\HateoasBundle\Metadata\RelationMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationContentMetadataInterface;
use FSC\HateoasBundle\Metadata\RelationsManagerInterface;
use FSC\HateoasBundle\Serializer\MetadataHelper;

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
    protected $relationsManager;
    protected $parametersFactory;
    protected $typeParser;
    protected $metadataHelper;
    protected $relationsJsonKey;
    protected $deferredEmbeds;

    public function __construct(
        ContentFactoryInterface $contentFactory,
        RelationsManagerInterface $relationsManager,
        ParametersFactoryInterface $parametersFactory,
        MetadataHelper $metadataHelper,
        TypeParser $typeParser = null,
        $relationsJsonKey = null
    ) {
        $this->contentFactory = $contentFactory;
        $this->relationsManager = $relationsManager;
        $this->parametersFactory = $parametersFactory;
        $this->metadataHelper = $metadataHelper;
        $this->typeParser = $typeParser ?: new TypeParser();
        $this->relationsJsonKey = $relationsJsonKey ?: 'relations';
        $this->deferredEmbeds = new \SplObjectStorage();
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

            $this->addRelationRelations($event, $relationContent, $relationMetadata);
            $node = $visitor->getNavigator()->accept(
                $relationContent,
                $this->getContentType($relationMetadata->getContent()),
                $event->getContext()
            );
            if (null !== $node) {
                $visitor->getCurrentNode()->appendChild($node);
            }

            $visitor->revertCurrentNode();
        }
    }

    public function onPostSerialize(Event $event)
    {
        if (null === ($relationsData = $this->getOnPostSerializeData($event))) {
            return;
        }

        $event->getVisitor()->addData($this->relationsJsonKey, $relationsData);
    }

    public function getOnPostSerializeData(Event $event)
    {
        $object = $event->getObject();
        $context = $event->getContext();

        $relationsContent = $this->contentFactory->create($object);

        $visitor = $event->getVisitor();

        $relationsData = array();
        if (null !== $relationsContent) {
            $context->startVisiting($object);
            foreach ($relationsContent as $relationMetadata) {
                $relationContent = $relationsContent[$relationMetadata];
                $this->addRelationRelations($event, $relationContent, $relationMetadata);
                $relationsData[$relationMetadata->getRel()] = $visitor->getNavigator()->accept(
                    $relationContent,
                    $this->getContentType($relationMetadata->getContent()),
                    $event->getContext()
                );
            }
            $context->stopVisiting($object);
        }

        if ($this->deferredEmbeds->contains($object)) {
            // $object contains inlined objects that had links

            $relationsData = array_merge($this->deferredEmbeds->offsetGet($object), $relationsData);
            $this->deferredEmbeds->detach($object);
        }

        $parentObjectInlining = $this->metadataHelper->getParentObjectInlining($object, $context);
        if (null !== $parentObjectInlining) {
            $this->defer($parentObjectInlining, $relationsData);

            return null;
        }

        return
            null !== $relationsContent || !empty($relationsData)
            ? $relationsData
            : null
        ;
    }

    protected function getRelationXmlElementName(RelationMetadataInterface $relationMetadata, $content)
    {
        $elementName = null;

        if (null !== $relationMetadata->getContent()->getSerializerXmlElementName()) {
            $elementName = $relationMetadata->getContent()->getSerializerXmlElementName();
        } elseif (null !== $relationMetadata->getContent()->getSerializerXmlElementRootName()) {
            $elementName = $this->metadataHelper->getXmlRootName($content) ?: $elementName;
        }

        if (null === $elementName && ('Pagerfanta\\PagerfantaInterface') && $content instanceof PagerfantaInterface) {
            $elementName = 'collection';
        } elseif (null === $elementName && ('Symfony\\Component\\Form\\FormView') && $content instanceof FormView) {
            $elementName = 'form';
        }

        return $elementName ?: 'relation';
    }

    protected function addRelationRelations(Event $event, $content, RelationMetadataInterface $relationMetadata)
    {
        $parameters = $this->parametersFactory->createParameters($event->getObject(), $relationMetadata->getParams());

        $this->relationsManager->addBasicRelations($content, $relationMetadata->getRoute(), $parameters);
    }

    protected function getContentType(RelationContentMetadataInterface $relationContentMetadata)
    {
        if (null === $relationContentMetadata->getSerializerType()) {
            return null;
        }

        return $this->typeParser->parse($relationContentMetadata->getSerializerType());
    }

    public function defer($parentObjectInlining, $relationsData)
    {
        if ($this->deferredEmbeds->contains($parentObjectInlining)) {
            $relationsData = array_merge($this->deferredEmbeds->offsetGet($parentObjectInlining), $relationsData);
        }

        // We need to defer the links serialization to the $parentObject
        $this->deferredEmbeds->attach($parentObjectInlining, $relationsData);
    }
}
