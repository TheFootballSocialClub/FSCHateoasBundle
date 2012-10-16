<?php

namespace FSC\HateoasBundle\Tests\Functional;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;

class SerializationTest extends TestCase
{
    public function testXML()
    {
        $user = new User();
        $user->setId(24);
        $user->setFirstName('Adrien');
        $user->setLastName('Brault');

        $this->assertSerializedXmlEquals(
'<user id="24">
  <first_name><![CDATA[Adrien]]></first_name>
  <last_name><![CDATA[Brault]]></last_name>
  <link rel="self" href="http://localhost/api/users/24"/>
  <link rel="alternate" href="http://localhost/profile/24"/>
  <link rel="users" href="http://localhost/api/users"/>
  <link rel="posts" href="http://localhost/api/users/24/posts"/>
  <relation rel="posts">
    <entry id="2">
      <title><![CDATA[How to create awesome symfony2 application]]></title>
      <link rel="self" href="http://localhost/api/posts/2"/>
    </entry>
    <entry id="1">
      <title><![CDATA[Welcome on the blog!]]></title>
      <link rel="self" href="http://localhost/api/posts/1"/>
    </entry>
  </relation>
</user>',
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
                    '},'.
                    '{'.
                        '"rel":"posts",'.
                        '"href":"http:\/\/localhost\/api\/users\/24\/posts"'.
                    '}'.
                '],'.
                '"relations":{'.
                    '"posts":['.
                        '{'.
                            '"id":2,'.
                            '"title":"How to create awesome symfony2 application",'.
                            '"links":['.
                                '{'.
                                    '"rel":"self",'.
                                    '"href":"http:\/\/localhost\/api\/posts\/2"'.
                                '}'.
                            ']'.
                        '},'.
                        '{'.
                            '"id":1,'.
                            '"title":"Welcome on the blog!",'.
                            '"links":['.
                                '{'.
                                    '"rel":"self",'.
                                    '"href":"http:\/\/localhost\/api\/posts\/1"'.
                                '}'.
                            ']'.
                        '}'.
                    ']'.
                '}'.
            '}',
            $user
        );
    }
}
