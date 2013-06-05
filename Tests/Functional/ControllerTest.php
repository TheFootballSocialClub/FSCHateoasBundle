<?php

namespace FSC\HateoasBundle\Tests\Functional;

/**
 * @group functional
 */
class ControllerTest extends TestCase
{
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

    public function testPutPostXml()
    {
        $client = $this->createClient();
        $client->request('PUT', '/api/posts/2');

        $response = $client->getResponse(); /**  */

        $this->assertEquals('http://localhost/api/posts/2', $response->headers->get('Location'));
    }

    public function testGetMixedElementNamesXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/mixed?_format=xml');

        $response = $client->getResponse(); /**  */

        $this->assertEquals(200, $response->getStatusCode());

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadXML($response->getContent());
        $xpath = new \DOMXPath($document);

        $nodeList = $xpath->query('/*/post');
        $this->assertEquals(2, $nodeList->length);

        $nodeList = $xpath->query('/*/user');
        $this->assertEquals(1, $nodeList->length);

        $nodeList = $xpath->query('/*/*');
        $this->assertEquals(6, $nodeList->length);
    }

    public function testGetCreatePostFormXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/posts/create?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<form method="POST" action="http://localhost/api/posts">
  <link rel="self" href="http://localhost/api/posts/create?_format=xml"/>
  <input type="text" name="post[title]" required="required"/>
</form>

XML
            , $response->getContent());
    }

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
  <link rel="adrienbrault" href="http://adrienbrault.fr"/>
</root>

XML
            , $response->getContent());
    }

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
  <link rel="adrienbrault" href="http://adrienbrault.fr"/>
</root>

XML
            , $response->getContent());
    }

    public function testGetPostJsonHal()
    {
        $client = $this->createClient(array('environment' => 'hal'));
        $client->request('GET', '/api/posts/2?_format=json');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $expectedJson = <<<JSON
{
    "id":2,
    "title":"How to create awesome symfony2 application",
    "_links":{
        "self":{"href":"http:\/\/localhost\/api\/posts\/2"}
    }
}
JSON;

        $this->assertEquals($this->removeJsonIndentation($expectedJson), $response->getContent());
    }

    /**
     * This test covers the case when an object has metadata attached but no relations/links
     */
    public function testEmptyActionJson()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/empty?_format=json');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<JSON
{"foo":"bar"}
JSON
            , $response->getContent());
    }

    public function testSerializingDoctrineProxiesToXML()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/users/proxy-pager?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<collection page="1" limit="10" total="2">
  <link rel="self" href="http://localhost/api/users/proxy-pager?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="first" href="http://localhost/api/users/proxy-pager?_format=xml&amp;limit=10&amp;page=1"/>
  <link rel="last" href="http://localhost/api/users/proxy-pager?_format=xml&amp;limit=10&amp;page=1"/>
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
</collection>

XML
        , $response->getContent());
    }

}
