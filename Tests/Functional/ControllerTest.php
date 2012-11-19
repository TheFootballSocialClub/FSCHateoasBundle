<?php

namespace FSC\HateoasBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

class ControllerTest extends TestCase
{
    /**
     * @group functional
     */
    public function testGetPostXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/2?_format=xml');

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

    /**
     * @group functional
     */
    public function testGetUserPostsXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/users/42/posts?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<posts page="1" limit="10" total="2">
  <link rel="self" href="http://localhost/api/users/42/posts?limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/users/42/posts?limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users/42/posts?limit=10&amp;page=1"/>
  <post id="2">
    <title><![CDATA[How to create awesome symfony2 application]]></title>
    <link rel="self" href="http://localhost/api/posts/2"/>
  </post>
  <post id="1">
    <title><![CDATA[Welcome on the blog!]]></title>
    <link rel="self" href="http://localhost/api/posts/1"/>
  </post>
</posts>

XML
        , $response->getContent());
    }

    /**
     * @group functional
     */
    public function testGetMixedElementNamesXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/mixed?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadXML($response->getContent());
        $xpath = new \DOMXPath($document);

        $nodeList = $xpath->query('/result/post');
        $this->assertEquals(2, $nodeList->length);

        $nodeList = $xpath->query('/result/user');
        $this->assertEquals(1, $nodeList->length);

        $nodeList = $xpath->query('/result/*');
        $this->assertEquals(3, $nodeList->length);
    }

    /**
     * @group functional
     */
    public function testGetCreatePostFormXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/create?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<form method="POST" action="http://localhost/api/posts">
  <link rel="self" href="http://localhost/api/posts/create"/>
  <input type="text" name="post[title]" required="required"/>
</form>

XML
        , $response->getContent());
    }

    /**
     * @group functional
     */
    public function testListPostsXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<posts page="1" limit="10" total="3">
  <link rel="self" href="http://localhost/api/posts?limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/posts?limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/posts?limit=10&amp;page=1"/>
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
  <link rel="create" href="http://localhost/api/posts/create"/>
  <form rel="create" method="POST" action="http://localhost/api/posts">
    <link rel="self" href="http://localhost/api/posts/create"/>
    <input type="text" name="post[title]" required="required"/>
  </form>
</posts>

XML
            , $response->getContent());
    }

    /**
     * @group functional
     */
    public function testRootControllerXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <link rel="users" href="http://localhost/api/users"/>
  <link rel="posts" href="http://localhost/api/posts"/>
</root>

XML
            , $response->getContent());
    }

    /**
     * @group functional
     */
    public function testRootRuntimeMetadataControllerXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/?_format=xml&user_id=1');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <link rel="users" href="http://localhost/api/users"/>
  <link rel="posts" href="http://localhost/api/posts"/>
  <link rel="me" href="http://localhost/api/users/1"/>
</root>

XML
            , $response->getContent());
    }

    public function testGetPostJsonHal()
    {
        $client = $this->createClient(array('env' => 'hal'));
        $client->request('GET', '/api/posts/2?_format=json');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSerializedJsonEquals('
          {
            "id":"2",
            "title":"How to create awesome symfony2 application",
            "_links":[
              {
                "rel":"self",
                "href":"http:\/\/localhost\/api\/posts\/2"
              }
            ]
          }',
          $response->getContent()
        );
    }
}
