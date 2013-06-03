<?php

namespace FSC\HateoasBundle\Tests\Functional;

use FSC\HateoasBundle\Tests\Functional\TestCase;
use FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;
use JMS\Serializer\Tests\Fixtures\SimpleObjectProxy;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @group functional
 */
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
  <link rel="last-post" href="http://localhost/api/users/24/last-post"/>
  <link rel="posts" href="http://localhost/api/users/24/posts"/>
  <link rel="alternate" href="http://localhost/api/users/24/alternate"/>
  <link rel="dynamic_href" href="this/is/a/href/from/a/property_path"/>
  <post rel="last-post" id="2">
    <title><![CDATA[How to create awesome symfony2 application]]></title>
    <link rel="self" href="http://localhost/api/posts/2"/>
  </post>
  <collection rel="posts" page="1" limit="1" total="2">
    <link rel="self" href="http://localhost/api/users/24/posts?limit=1&amp;page=1"/>
    <link rel="first" href="http://localhost/api/users/24/posts?limit=1&amp;page=1"/>
    <link rel="last" href="http://localhost/api/users/24/posts?limit=1&amp;page=2"/>
    <link rel="next" href="http://localhost/api/users/24/posts?limit=1&amp;page=2"/>
    <post id="2">
      <title><![CDATA[How to create awesome symfony2 application]]></title>
      <link rel="self" href="http://localhost/api/posts/2"/>
    </post>
  </collection>
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
'{
    "id": 24,
    "first_name": "Adrien",
    "last_name": "Brault",
    "links": {
        "self": {
            "href": "http:\/\/localhost\/api\/users\/24"
        },
        "alternate": [
            {
                "href": "http:\/\/localhost\/profile\/24"
            },
            {
                "href": "http:\/\/localhost\/api\/users\/24\/alternate"
            }
        ],
        "users": {
            "href": "http:\/\/localhost\/api\/users"
        },
        "last-post": {
            "href": "http:\/\/localhost\/api\/users\/24\/last-post"
        },
        "posts": {
            "href": "http:\/\/localhost\/api\/users\/24\/posts"
        },
        "dynamic_href": {
            "href": "this\/is\/a\/href\/from\/a\/property_path"
        }
    },
    "relations": {
        "last-post": {
            "id": 2,
            "title": "How to create awesome symfony2 application",
            "links": {
                "self": {
                    "href": "http:\/\/localhost\/api\/posts\/2"
                }
            }
        },
        "posts": {
            "page": 1,
            "limit": 1,
            "total": 2,
            "results": [
                {
                    "id": 2,
                    "title": "How to create awesome symfony2 application",
                    "links": {
                        "self": {
                            "href": "http:\/\/localhost\/api\/posts\/2"
                        }
                    }
                }
            ],
            "links": {
                "self": {
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=1"
                },
                "first": {
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=1"
                },
                "last": {
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=2"
                },
                "next": {
                    "href": "http:\/\/localhost\/api\/users\/24\/posts?limit=1&page=2"
                }
            }
        }
    }
}',
            $user
        );
    }

    public function testSerializingDoctrineProxiesToXML()
    {
        require __DIR__ . "/TestBundle/Model/UserProxy.php";

        $user1 = new \Proxies\__CG__\FSC\HateoasBundle\Tests\Functional\TestBundle\Model\User;
        $user2 = User::create(24, 'Adrien', 'Brault');

        $results = array(
            $user1,
            $user2
        );

        $pager = new Pagerfanta(new ArrayAdapter($results));

        $this->assertSerializedXmlEquals(
'<collection page="1" limit="10" total="2">
  <user id="1">
    <first_name><![CDATA[Ruud]]></first_name>
    <last_name><![CDATA[Kamphuis]]></last_name>
    <link rel="self" href="http://localhost/api/users/1"/>
    <link rel="alternate" href="http://localhost/profile/1"/>
    <link rel="users" href="http://localhost/api/users"/>
    <link rel="last-post" href="http://localhost/api/users/1/last-post"/>
    <link rel="posts" href="http://localhost/api/users/1/posts"/>
    <link rel="alternate" href="http://localhost/api/users/1/alternate"/>
    <link rel="dynamic_href" href="this/is/a/href/from/a/property_path"/>
    <post rel="last-post" id="2">
      <title><![CDATA[How to create awesome symfony2 application]]></title>
      <link rel="self" href="http://localhost/api/posts/2"/>
    </post>
    <collection rel="posts" page="1" limit="1" total="2">
      <link rel="self" href="http://localhost/api/users/1/posts?limit=1&amp;page=1"/>
      <link rel="first" href="http://localhost/api/users/1/posts?limit=1&amp;page=1"/>
      <link rel="last" href="http://localhost/api/users/1/posts?limit=1&amp;page=2"/>
      <link rel="next" href="http://localhost/api/users/1/posts?limit=1&amp;page=2"/>
      <post id="2">
        <title><![CDATA[How to create awesome symfony2 application]]></title>
        <link rel="self" href="http://localhost/api/posts/2"/>
      </post>
    </collection>
  </user>
  <user id="24">
    <first_name><![CDATA[Adrien]]></first_name>
    <last_name><![CDATA[Brault]]></last_name>
    <link rel="self" href="http://localhost/api/users/24"/>
    <link rel="alternate" href="http://localhost/profile/24"/>
    <link rel="users" href="http://localhost/api/users"/>
    <link rel="last-post" href="http://localhost/api/users/24/last-post"/>
    <link rel="posts" href="http://localhost/api/users/24/posts"/>
    <link rel="alternate" href="http://localhost/api/users/24/alternate"/>
    <link rel="dynamic_href" href="this/is/a/href/from/a/property_path"/>
    <post rel="last-post" id="2">
      <title><![CDATA[How to create awesome symfony2 application]]></title>
      <link rel="self" href="http://localhost/api/posts/2"/>
    </post>
    <collection rel="posts" page="1" limit="1" total="2">
      <link rel="self" href="http://localhost/api/users/24/posts?limit=1&amp;page=1"/>
      <link rel="first" href="http://localhost/api/users/24/posts?limit=1&amp;page=1"/>
      <link rel="last" href="http://localhost/api/users/24/posts?limit=1&amp;page=2"/>
      <link rel="next" href="http://localhost/api/users/24/posts?limit=1&amp;page=2"/>
      <post id="2">
        <title><![CDATA[How to create awesome symfony2 application]]></title>
        <link rel="self" href="http://localhost/api/posts/2"/>
      </post>
    </collection>
  </user>
</collection>',
            $pager
        );
    }
}
