<?php

namespace FSC\HateoasBundle\Tests\Functional\Serializer\EventSubscriber;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;

class LinkEventSubscriberTest extends TestCase
{
    public function testXML()
    {
        $user = new User();
        $user->setId(24);
        $user->setFirstName('Adrien');
        $user->setLastName('Brault');

        $this->assertSerializedXmlEquals(
'<result id="24">
  <first_name><![CDATA[Adrien]]></first_name>
  <last_name><![CDATA[Brault]]></last_name>
  <link rel="self" href="http://localhost/api/users/24"/>
  <link rel="alternate" href="http://localhost/profile/24"/>
  <link rel="users" href="http://localhost/api/users"/>
</result>',
            $user
        );
    }

    public function testJSON()
    {
        $user = new User();
        $user->setId(24);
        $user->setFirstName('Adrien');
        $user->setLastName('Brault');

        $this->assertSerializedJsonEquals(
'{'.
    '"id":24,'.
    '"first_name":"Adrien",'.
    '"last_name":"Brault",'.
    '"links":['.
        '{'.
            '"rel":"self",'.
            '"href":"http:\/\/localhost\/api\/users\/24"'.
        '},'.
        '{'.
            '"rel":"alternate",'.
            '"href":"http:\/\/localhost\/profile\/24"'.
        '},'.
        '{'.
            '"rel":"users",'.
            '"href":"http:\/\/localhost\/api\/users"'.
        '}'.
    ']'.
'}',
            $user
        );
    }
}
