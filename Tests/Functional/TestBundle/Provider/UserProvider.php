<?php

namespace FSC\HateoasBundle\Tests\Functional\TestBundle\Provider;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;

class UserProvider
{
    public function getUser($id)
    {
        switch ($id) {
            case 1: return User::create($id, 'Adrien', 'Brault');
            default: return User::create($id);
        }
    }
}
