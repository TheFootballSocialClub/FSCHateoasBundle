<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

class Post
{
    private $id;
    private $title;

    public function setId($id)
    {
        $this->id = (int) $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public static function create($id, $title)
    {
        $post = new static();
        $post->setId($id);
        $post->setTitle($title);

        return $post;
    }
}
