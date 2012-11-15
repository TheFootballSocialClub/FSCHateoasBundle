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
        $this->assertEquals(
'<?xml version="1.0" encoding="UTF-8"?>
<post id="2">
  <title><![CDATA[How to create awesome symfony2 application]]></title>
  <link rel="self" href="http://localhost/api/posts/2"/>
</post>
',
        $response->getContent());
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
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>
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
',
            $response->getContent());
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
}
