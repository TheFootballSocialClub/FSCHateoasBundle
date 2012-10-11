<?php

namespace FSC\HateoasBundle\Tests\Functional\Serializer\EventSubscriber;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;

class LinkEventSubscriberTest extends TestCase
{
    public function testXML()
    {
        $user = new User();
        $user->setFirstName('Adrien');
        $user->setLastName('Brault');

        $this->assertSerializedXmlEquals(
'<result>
  <first_name><![CDATA[Adrien]]></first_name>
  <last_name><![CDATA[Brault]]></last_name>
  <link rel="self" href="http://symfony.com/hey"/>
  <link rel="alternate" href="http://symfony.com/fabpot"/>
</result>',
            $user
        );
    }

    public function testJSON()
    {
        $user = new User();
        $user->setFirstName('Adrien');
        $user->setLastName('Brault');

        $this->assertSerializedJsonEquals(
'{'.
    '"first_name":"Adrien",'.
    '"last_name":"Brault",'.
    '"links":{'.
        '"self":{'.
            '"rel":"self",'.
            '"href":"http:\/\/symfony.com\/hey"'.
        '},'.
        '"alternate":{'.
            '"rel":"alternate",'.
            '"href":"http:\/\/symfony.com\/fabpot"'.
        '}'.
    '}'.
'}',
            $user
        );
    }
}
