<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\EventDispatcher\Event;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\GenericSerializationVisitor;

use FSC\HateoasBundle\Model\HalPagerfanta;
use FSC\HateoasBundle\Model\PagedCollectionInterface;
use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;

class HalPagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (array('json', 'xml', 'yml') as $format) {
            $methods[] = array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format'    => $format,
                'type'      => "FSC\HateoasBundle\Model\PagedCollectionInterface",
                'method'    => 'serializeTo'.('xml' == $format ? 'XML' : 'Array')
            );
        }

        return $methods;
    }

    protected $embedderEventSubscriber;
    protected $linkEventSubscriber;
    protected $linksJsonKey;
    protected $relationsJsonKey;

    public function __construct(
        EmbedderEventSubscriber $embedderEventSubscriber,
        LinkEventSubscriber $linkEventSubscriber,
        $linksKey = null,
        $relationsKey = null
    ) {
        $this->embedderEventSubscriber = $embedderEventSubscriber;
        $this->linkEventSubscriber = $linkEventSubscriber;
        $this->linksJsonKey = $linksKey ?: 'links';
        $this->relationsJsonKey = $relationsKey ?: 'relations';
    }

    public function serializeToXML(XmlSerializationVisitor $visitor, PagedCollectionInterface $collection, array $type)
    {
        return $visitor->getNavigator()->accept($collection->getPager(), null, $visitor);
    }

    public function serializeToArray(GenericSerializationVisitor $visitor, PagedCollectionInterface $collection, array $type)
    {
        $shouldSetRoot = null === $visitor->getRoot();

        $pager = $collection->getPager();

        $data = array(
            'page'  => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->getNbResults(),
        );

        // Add the links from the pager
        if (null !== ($links = $this->linkEventSubscriber->getOnPostSerializeData(new Event($visitor, $pager, $type)))) {
            $exisitingLinks            = !empty($data[$this->linksJsonKey]) ? $data[$this->linksJsonKey] : array();
            $data[$this->linksJsonKey] = array_merge($exisitingLinks, $links);
        }

        // Add the links from the collection
        if (null !== ($links = $this->linkEventSubscriber->getOnPostSerializeData(new Event($visitor, $collection, $type)))) {
            $exisitingLinks            = !empty($data[$this->linksJsonKey]) ? $data[$this->linksJsonKey] : array();
            $data[$this->linksJsonKey] = array_merge($exisitingLinks, $links);
        }

        // Add the embedded relations
        if (null !== ($relations = $this->embedderEventSubscriber->getOnPostSerializeData(new Event($visitor, $pager, $type)))) {
            $data[$this->relationsJsonKey] = $relations;
        }

        $resultsType = isset($type['params'][0]) ? $type['params'][0] : null;
        $data[$this->relationsJsonKey][$collection->getRel()] = $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $visitor);

        if ($shouldSetRoot) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}
