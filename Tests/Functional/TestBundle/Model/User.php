<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Model;

class User
{
    private $id;
    private $firstName;
    private $lastName;

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public static function create($id, $firstName = null, $lastName = null)
    {
        $user = new static();
        $user->setId($id);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        return $user;
    }

    public function getDynamicHref()
    {
        return "this/is/a/href/from/a/property_path";
    }
}
