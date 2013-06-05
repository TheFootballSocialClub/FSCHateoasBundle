<?php

namespace FSC\HateoasBundle\Tests\Functional;

/**
 * @group functional
 */
class RelationsTest extends TestCase
{
    public function testTemplated()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/2/templated?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<post id="2">
  <title><![CDATA[How to create awesome symfony2 application]]></title>
  <link rel="self" href="http://localhost/api/posts/2"/>
  <link rel="search" href="search?{&amp;q}" templated="true"/>
</post>

XML
            , $response->getContent());
    }

    public function testDifferentRouter()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/2/alternate_router?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<post id="2">
  <title><![CDATA[How to create awesome symfony2 application]]></title>
  <link rel="self" href="http://localhost/api/posts/2"/>
  <link rel="alternate_router" href="PREPENDhttp://localhost/api/posts/2"/>
  <link rel="alternate_router_relative" href="PREPEND/api/posts/2"/>
</post>

XML
            , $response->getContent());
    }

    public function testExcludingLinksConditionTrue()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/2/exclude?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<post id="2">
  <title><![CDATA[How to create awesome symfony2 application]]></title>
  <link rel="self" href="http://localhost/api/posts/2"/>
</post>

XML
            , $response->getContent());
    }

    public function testExcludingLinksConditionFalse()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/1/exclude?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<post id="1">
  <title><![CDATA[Welcome on the blog!]]></title>
  <link rel="self" href="http://localhost/api/posts/1"/>
  <link rel="parent" href="http://localhost/api/posts/2"/>
</post>

XML
            , $response->getContent());
    }

    public function testGetCreatePostWithFormatInLinksFormXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/create_format?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<form method="POST" action="http://localhost/api/posts?_format=xml">
  <link rel="self" href="http://localhost/api/posts/create_format?_format=xml"/>
  <input type="text" name="post[title]" required="required"/>
</form>

XML
            , $response->getContent());
    }

    public function testGetRelationsJsonHal()
    {
        $client = $this->createClient(array('environment' => 'hal'));
        $client->request('GET', '/api/mixed?_format=json');

        $response = $client->getResponse();

        $expectedJson = <<<JSON
{
    "page":1,
    "limit":10,
    "total":3,
    "results":[
        {
            "id":1,
            "title":"Welcome on the blog!",
            "_links":{
                "self":{"href":"http:\/\/localhost\/api\/posts\/1"}
            }
        },
        {
            "id":2,
            "title":"How to create awesome symfony2 application",
            "_links":{
                "self":{"href":"http:\/\/localhost\/api\/posts\/2"}
            }
        },
        {
            "id":1,
            "first_name":"Adrien",
            "last_name":"Brault",
            "_links":{
                "self":{"href":"http:\/\/localhost\/api\/users\/1"},
                "alternate":[
                    {"href":"http:\/\/localhost\/profile\/1"},
                    {"href":"http:\/\/localhost\/api\/users\/1\/alternate"}
                ],
                "users":{"href":"http:\/\/localhost\/api\/users"},
                "last-post":{"href":"http:\/\/localhost\/api\/users\/1\/last-post"},
                "posts":{"href":"http:\/\/localhost\/api\/users\/1\/posts"},
                "dynamic_href":{"href":"this\/is\/a\/href\/from\/a\/property_path"}
            },
            "_embedded":{
                "last-post":{
                    "id":2,
                    "title":"How to create awesome symfony2 application",
                    "_links":{
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
                            "_links":{
                                "self":{"href":"http:\/\/localhost\/api\/posts\/2"}
                            }
                        }
                    ],
                    "_links":{
                        "self":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=1"},
                        "first":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=1"},
                        "last":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=2"},
                        "next":{"href":"http:\/\/localhost\/api\/users\/1\/posts?limit=1&page=2"}
                    }
                }
            }
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($this->removeJsonIndentation($expectedJson), $response->getContent());
    }

    public function testAutoAddingBasicRelationsForPagerXML()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/pager?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<collection page="1" limit="10" total="3">
  <link rel="self" href="http://localhost/api/posts/pager?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/posts/pager?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/posts/pager?_format=xml&amp;limit=10&amp;page=1"/>
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
</collection>

XML
            , $response->getContent());
    }

    public function testAutoAddingBasicRelationsForPagerJSON()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/pager?_format=json');

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
                "self":{"href":"http:\/\/localhost\/api\/posts\/1"}
            }
        },
        {
            "id":2,
            "title":"How to create awesome symfony2 application",
            "links":{
                "self":{"href":"http:\/\/localhost\/api\/posts\/2"}
            }
        },
        {
            "id":3,
            "title":"",
            "links":{
                "self":{"href":"http:\/\/localhost\/api\/posts\/3"}
            }
        }
    ],
    "links":{
        "self":{"href":"http:\/\/localhost\/api\/posts\/pager?_format=json&limit=10&page=1"},
        "first":{"href":"http:\/\/localhost\/api\/posts\/pager?_format=json&limit=10&page=1"},
        "last":{"href":"http:\/\/localhost\/api\/posts\/pager?_format=json&limit=10&page=1"}
    }
}
JSON;
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($this->removeJsonIndentation($expectedJson), $response->getContent());
    }
}