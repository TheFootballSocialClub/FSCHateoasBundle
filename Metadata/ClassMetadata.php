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
     *         ),
     *         'content_provider' => array(
     *             'id' => 'some.service',
     *             'method' => 'getSomething',
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
    protected $relations = array();

    public function getRelations()
    {
        return $this->relations;
    }

    public function setRelations(array $relations)
    {
        $this->relations = $relations;
    }

    public function serialize()
    {
        return serialize(array(
            $this->relations,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->relations,
            $parentStr
        ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
