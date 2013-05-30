<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\GenericSerializationVisitor;
use JMS\Serializer\Context;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Metadata\MetadataFactoryInterface;
use Pagerfanta\Pagerfanta;
use Doctrine\Common\Persistence\Proxy;

use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;

class PagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => 'Pagerfanta\Pagerfanta',
                'method' => 'serializeTo'.('xml' == $format ? 'XML' : 'Array'),
            );
        }

        return $methods;
    }

    protected $serializerMetadataFactory;
    protected $embedderEventSubscriber;
    protected $linkEventSubscriber;
    protected $xmlElementsNamesUseSerializerMetadata;
    protected $linksJsonKey;
    protected $relationsJsonKey;

    public function __construct(
        MetadataFactoryInterface $serializerMetadataFactory,
        EmbedderEventSubscriber $embedderEventSubscriber,
        LinkEventSubscriber $linkEventSubscriber,
        $xmlElementsNamesUseSerializerMetadata = true,
        $linksKey = null,
        $relationsKey = null
    ) {
        $this->serializerMetadataFactory = $serializerMetadataFactory;
        $this->embedderEventSubscriber = $embedderEventSubscriber;
        $this->linkEventSubscriber = $linkEventSubscriber;
        $this->xmlElementsNamesUseSerializerMetadata = $xmlElementsNamesUseSerializerMetadata;
        $this->linksJsonKey = $linksKey ?: 'links';
        $this->relationsJsonKey = $relationsKey ?: 'relations';
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, Pagerfanta $pager, array $type, Context $context)
    {
        if (null === $visitor->document) {
            $visitorClass = new \ReflectionClass(get_class($visitor));
            $defaultRootNameProperty = $visitorClass->getProperty('defaultRootName');
            $defaultRootNameProperty->setAccessible(true);
            if ('result' === $defaultRootNameProperty->getValue($visitor)) {
                $visitor->setDefaultRootName('collection');
            }

            $visitor->document = $visitor->createDocument();
        }

        $this->embedderEventSubscriber->onPostSerializeXML(new ObjectEvent($context, $pager, $type));
        $this->linkEventSubscriber->onPostSerializeXML(new ObjectEvent($context, $pager, $type));

        $currentNode = $visitor->getCurrentNode(); /** @var $currentNode \DOMElement */
        $currentNode->setAttribute('page', $pager->getCurrentPage());
        $currentNode->setAttribute('limit', $pager->getMaxPerPage());
        $currentNode->setAttribute('total', $pager->getNbResults());

        $resultsType = isset($type['params'][0]) ? $type['params'][0] : null;

        if (!$this->xmlElementsNamesUseSerializerMetadata) {
            return $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $context);
        }

        foreach ($pager->getCurrentPageResults() as $result) {
            $elementName = 'entry';
            if (is_object($result)) {
                $className = get_class($result);

                // Handle Doctrine proxies differently
                if ($result instanceof Proxy) {
                    $className = get_parent_class($result);
                }

                if(null !== ($resultMetadata = $this->serializerMetadataFactory->getMetadataForClass($className))) {
                    $elementName = $resultMetadata->xmlRootName ?: $elementName;
                }
            }

            $entryNode = $visitor->getDocument()->createElement($elementName);
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            if (null !== $node = $visitor->getNavigator()->accept($result, $resultsType, $context)) {
                $visitor->getCurrentNode()->appendChild($node);
            }

            $visitor->revertCurrentNode();
        }
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, Pagerfanta $pager, array $type, Context $context)
    {
        $resultsType = array(
            'name' => 'array',
        );
        if (isset($type['params'])) {
            $resultsType['params'] = $type['params'];
        }

        $shouldSetRoot = null === $visitor->getRoot();

        $data = array(
            'page' => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->getNbResults(),
            'results' => $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $context),
        );

        $context->stopVisiting($pager); // Make sure the visiting behavior is the same as for normal events to call the getOnPostSerializeData

        if (null !== ($links = $this->linkEventSubscriber->getOnPostSerializeData(new ObjectEvent($context, $pager, $type)))) {
            $data[$this->linksJsonKey] = $links;
        }

        if (null !== ($relations = $this->embedderEventSubscriber->getOnPostSerializeData(new ObjectEvent($context, $pager, $type)))) {
            $data[$this->relationsJsonKey] = $relations;
        }

        $context->startVisiting($pager);

        if ($shouldSetRoot) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}
