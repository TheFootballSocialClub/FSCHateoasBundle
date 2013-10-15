<?php

namespace FSC\HateoasBundle\Tests\Functional;

/**
 * @group functional
 */
class PaginationTest extends TestCase
{
    public function testGetUserPostsXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/users/42/posts?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<collection page="1" limit="10" total="2">
  <link rel="self" href="http://localhost/api/users/42/posts?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/users/42/posts?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users/42/posts?_format=xml&amp;limit=10&amp;page=1"/>
  <post id="2">
    <title><![CDATA[How to create awesome symfony2 application]]></title>
    <link rel="self" href="http://localhost/api/posts/2"/>
  </post>
  <post id="1">
    <title><![CDATA[Welcome on the blog!]]></title>
    <link rel="self" href="http://localhost/api/posts/1"/>
  </post>
</collection>

XML
            , $response->getContent());
    }

    public function testGetUserPostsConfig1Xml()
    {
        $client = $this->createClient(array('environment' => 'test1'));
        $client->request('GET', '/api/users/42/posts?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<collection page="1" limit="10" total="2">
  <link rel="self" href="http://localhost/api/users/42/posts?_format=xml&amp;pagination%5Blimit%5D=10&amp;pagination%5Bpage%5D=1"/>
  <link rel="first" href="http://localhost/api/users/42/posts?_format=xml&amp;pagination%5Blimit%5D=10&amp;pagination%5Bpage%5D=1"/>
  <link rel="last" href="http://localhost/api/users/42/posts?_format=xml&amp;pagination%5Blimit%5D=10&amp;pagination%5Bpage%5D=1"/>
  <post id="2">
    <title><![CDATA[How to create awesome symfony2 application]]></title>
    <link rel="self" href="http://localhost/api/posts/2"/>
  </post>
  <post id="1">
    <title><![CDATA[Welcome on the blog!]]></title>
    <link rel="self" href="http://localhost/api/posts/1"/>
  </post>
</collection>

XML
            , $response->getContent());
    }

    /**
     * Test to make sure that inlining craziness work
     *
     * The example in itself does not make much sense
     */
    public function testListPostsXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<posts page="1" limit="10" total="3" id="1">
  <link rel="self" href="http://localhost/api/posts?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/posts?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/posts?_format=xml&amp;limit=10&amp;page=1"/>
  <post id="1">
    <title><![CDATA[Welcome on the blog!]]></title>
    <link rel="self" href="http://localhost/api/posts/1"/>
  </post>
  <post id="2">
    <title><![CDATA[How to create awesome symfony2 application]]></title>
    <link rel="self" href="http://localhost/api/posts/2"/>
  </post>
  <post id="3">
    <title><![CDATA[]]></title>
    <link rel="self" href="http://localhost/api/posts/3"/>
  </post>
  <first_name><![CDATA[Adrien]]></first_name>
  <last_name><![CDATA[Brault]]></last_name>
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
  <link rel="create" href="http://localhost/api/posts/create"/>
  <form rel="create" method="POST" action="http://localhost/api/posts">
    <link rel="self" href="http://localhost/api/posts/create"/>
    <input type="text" name="post[title]" required="required"/>
  </form>
</posts>

XML
            , $response->getContent());
    }

    public function testHalPagerInXML()
    {
        $client = $this->createClient(array('environment' => 'hal'));
        $client->request('GET', '/api/pager?_format=xml');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<collection page="1" limit="10" total="2">
  <link rel="self" href="http://localhost/api/pager?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/pager?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/pager?_format=xml&amp;limit=10&amp;page=1"/>
  <entry>
    <entry><![CDATA[value]]></entry>
  </entry>
  <entry>
    <entry><![CDATA[value]]></entry>
  </entry>
</collection>

XML
            , $response->getContent());
    }

    public function testHalPager()
    {
        $client = $this->createClient(array('environment' => 'hal'));
        $client->request('GET', '/api/pager?_format=json');

        $response = $client->getResponse();

        $expectedJson = <<<JSON
{
    "page":1,
    "limit":10,
    "total":2,
    "_links":{
        "self":{"href":"http:\/\/localhost\/api\/pager?_format=json&limit=10&page=1"},
        "first":{"href":"http:\/\/localhost\/api\/pager?_format=json&limit=10&page=1"},
        "last":{"href":"http:\/\/localhost\/api\/pager?_format=json&limit=10&page=1"}
    },
    "_embedded":{
        "test-rel":[
            {"first":"value"},
            {"second":"value"}
        ]
    }
}
JSON;
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($this->removeJsonIndentation($expectedJson), $response->getContent());
    }

    public function testListPostsJson()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts?_format=json');

        $response = $client->getResponse(); /** @var $response Response */

        $expectedJson = <<<JSON
{
    "page":1,
    "limit":10,
    "total":3,
    "results":[
        {
            "id":1,
            "title":"Welcome on the blog!",
            "links":{
                "self":{
                    "href":"http:\/\/localhost\/api\/posts\/1"
                }
            }
        },
        {
            "id":2,
            "title":"How to create awesome symfony2 application",
            "links":{
                "self":{
                    "href":"http:\/\/localhost\/api\/posts\/2"
                }
            }
        },
        {
            "id":3,
            "title":"",
            "links":{
                "self":{
                    "href":"http:\/\/localhost\/api\/posts\/3"
                }
            }
        }
    ],
    "id":1,
    "first_name":"Adrien",
    "last_name":"Brault",
    "links":{
        "self":[
            {"href":"http:\/\/localhost\/api\/posts?_format=json&limit=10&page=1"},
            {"href":"http:\/\/localhost\/api\/users\/1"}
        ],
        "first":{
            "href":"http:\/\/localhost\/api\/posts?_format=json&limit=10&page=1"
        },
        "last":{
            "href":"http:\/\/localhost\/api\/posts?_format=json&limit=10&page=1"
        },
        "alternate":[
            {"href":"http:\/\/localhost\/profile\/1"},
            {"href":"http:\/\/localhost\/api\/users\/1\/alternate"}
        ],
        "users":{"href":"http:\/\/localhost\/api\/users"},
        "last-post":{"href":"http:\/\/localhost\/api\/users\/1\/last-post"},
        "posts":{"href":"http:\/\/localhost\/api\/users\/1\/posts"},
        "dynamic_href":{"href":"this\/is\/a\/href\/from\/a\/property_path"},
        "create":{
            "href":"http:\/\/localhost\/api\/posts\/create"
        }
    },
    "relations":{
        "last-post":{
            "id":2,
            "title":"How to create awesome symfony2 application",
            "links":{
                "self":{"href":"http:\/\/localhost\/api\/posts\/2"}
            }
        },
        "posts":{
            "page":1,
            "limit":1,
            "total":2,
            "results":[
                {
                    "id":2,
                    "title":"How to create awesome symfony2 application",
                    "links":{
                        "self":{"href":"http:\/\/localhost\/api\/posts\/2"}
                    }
                }
            ],
            "links":{
                "self":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=1"},
                "first":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=1"},
                "last":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=2"},
                "next":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=2"}
            }
        },
        "create":null
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($this->removeJsonIndentation($expectedJson), $response->getContent());
    }

}
