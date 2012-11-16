<?php

namespace FSC\HateoasBundle\Tests\Functional;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;

class SerializationTest extends TestCase
{
    /**
     * @group functional
     */
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
  <link rel="last-post" href="http://localhost/api/users/24/last-post"/>
  <link rel="posts" href="http://localhost/api/users/24/posts"/>
  <post rel="last-post" id="2">
    <title><![CDATA[How to create awesome symfony2 application]]></title>
    <link rel="self" href="http://localhost/api/posts/2"/>
  </post>
  <posts rel="posts" page="1" limit="1" total="2">
    <link rel="self" href="http://localhost/api/users/24/posts?limit=1&amp;page=1"/>
    <link rel="first" href="http://localhost/api/users/24/posts?limit=1&amp;page=1"/>
    <link rel="last" href="http://localhost/api/users/24/posts?limit=1&amp;page=2"/>
    <link rel="next" href="http://localhost/api/users/24/posts?limit=1&amp;page=2"/>
    <post id="2">
      <title><![CDATA[How to create awesome symfony2 application]]></title>
      <link rel="self" href="http://localhost/api/posts/2"/>
    </post>
  </posts>
</user>',
            $user
        );
    }

    /**
     * @group functional
     */
    public function testJSON()
    {
        $user = new User();
        $user->setId(24);
        $user->setFirstName('Adrien');
        $user->setLastName('Brault');

        $this->assertSerializedJsonEquals(
'{
    "id": 24,
    "first_name": "Adrien",
    "last_name": "Brault",
    "links": [
        {
            "rel": "self",
            "href": "http:\/\/localhost\/api\/users\/24"
        },
        {
            "rel": "alternate",
            "href": "http:\/\/localhost\/profile\/24"
        },
        {
            "rel": "users",
            "href": "http:\/\/localhost\/api\/users"
        },
        {
            "rel": "last-post",
            "href": "http:\/\/localhost\/api\/users\/24\/last-post"
        },
        {
            "rel": "posts",
            "href": "http:\/\/localhost\/api\/users\/24\/posts"
        }
    ],
    "relations": {
        "last-post": {
            "id": 2,
            "title": "How to create awesome symfony2 application",
            "links": [
                {
                    "rel": "self",
                    "href": "http:\/\/localhost\/api\/posts\/2"
                }
            ]
        },
        "posts": {
            "page": 1,
            "limit": 1,
            "total": 2,
            "results": [
                {
                    "id": 2,
                    "title": "How to create awesome symfony2 application",
                    "links": [
                        {
                            "rel": "self",
                            "href": "http:\/\/localhost\/api\/posts\/2"
                        }
                    ]
                }
            ],
            "links": [
                {
                    "rel": "self",
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=1"
                },
                {
                    "rel": "first",
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=1"
                },
                {
                    "rel": "last",
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=2"
                },
                {
                    "rel": "next",
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=2"
                }
            ]
        }
    }
}',
            $user
        );
    }
}
