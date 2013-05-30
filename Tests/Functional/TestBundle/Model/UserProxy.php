<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

use Doctrine\Common\Persistence\Proxy;

class UserProxy extends User implements Proxy
{
    public $__isInitialized__ = false;

    public function __load()
    {
        if (!$this->__isInitialized__) {
            $this->id = 1;
            $this->firstName = "Ruud";
            $this->lastName = "Kamphuis";

            $this->__isInitialized__ = true;
        }
    }

    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }
}