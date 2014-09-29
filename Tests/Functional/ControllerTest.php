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
        $this->assertEquals(3, $nodeList->length);
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

    public function testRootControllerDisabledLinksXml()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/disabled_links?_format=xml');

        $response = $client->getResponse(); /** @var $response Response */

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root/>

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

}
