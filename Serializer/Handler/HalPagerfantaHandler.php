<?php

namespace FSC\HateoasBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\EventDispatcher\Event;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\XmlSerializationVisitor;
use JMS\Serializer\GenericSerializationVisitor;
use Metadata\MetadataFactoryInterface;
use Pagerfanta\Pagerfanta;

use FSC\HateoasBundle\Model\HalPagerfanta;

use FSC\HateoasBundle\Serializer\EventSubscriber\EmbedderEventSubscriber;
use FSC\HateoasBundle\Serializer\EventSubscriber\LinkEventSubscriber;

class HalPagerfantaHandler implements SubscribingHandlerInterface
{
    public static function getSubscribingMethods()
    {
        return array(
            array(
            'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
            'format' => 'json',
            'type' => 'FSC\HateoasBundle\Model\HalPagerfanta',
            'method' => 'serializeToArray',
        )
        );
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

    public function serializeToArray(GenericSerializationVisitor $visitor, HalPagerfanta $pager, array $type)
    {
        $resultsType = isset($type['params'][0]) ? $type['params'][0] : null;

        $shouldSetRoot = null === $visitor->getRoot();

        $data = array(
            'page' => $pager->getCurrentPage(),
            'limit' => $pager->getMaxPerPage(),
            'total' => $pager->getNbResults(),
        );

        if (null !== ($links = $this->linkEventSubscriber->getOnPostSerializeData(new Event($visitor, $pager, $type)))) {
            $data[$this->linksJsonKey] = $links;
        }

        if (null !== ($relations = $this->embedderEventSubscriber->getOnPostSerializeData(new Event($visitor, $pager, $type)))) {
            $data[$this->relationsJsonKey] = $relations;
        }

        $data[$this->relationsJsonKey][$pager->rel] = $visitor->getNavigator()->accept($pager->getCurrentPageResults(), $resultsType, $visitor);

        if ($shouldSetRoot) {
            $visitor->setRoot($data);
        }

        return $data;
    }
}