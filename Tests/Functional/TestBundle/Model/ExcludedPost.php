<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

class ExcludedPost extends Post
{
    protected $parent;

    public static function create($id, $title, $parent = null)
    {
        $post = parent::create($id, $title);
        $post->setParent($parent);

        return $post;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }
}