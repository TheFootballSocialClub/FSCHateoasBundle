<?php

namespace FSC\HateoasBundle\Metadata;

use Metadata\MergeableClassMetadata;

class ClassMetadata extends MergeableClassMetadata implements ClassMetadataInterface
{
    /**
     * @var array
     *
     * like
     *
     * array(
     *     array(
     *         'rel' => 'xxx',
     *         'route' => 'xxx',
     *         'params' => array(
     *             'name' => 'propertyPath',
     *         )
     *     ),
     *     array(
     *         'rel' => 'xxx',
     *         'route' => 'xxx',
     *         'params' => array(
     *             'name' => 'propertyPath',
     *         )
     *     ),
     * )
     */
    protected $links = array();

    public function getLinks()
    {
        return $this->links;
    }

    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    public function serialize()
    {
        return serialize(array(
            $this->links,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->links,
            $parentStr
        ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
