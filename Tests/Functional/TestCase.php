<?php

namespace FSC\HateoasBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use JMS\SerializerBundle\Serializer\Serializer;

class TestCase extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        $env = @$options['env'] ?: 'test';

        return new AppKernel($env, true);
    }

    protected static function initializeKernel(array $options = array())
    {
        if (null !== static::$kernel) {
            return;
        }

        static::$kernel = static::createKernel($options);
        static::$kernel->boot();
    }

    protected static function getKernel()
    {
        static::initializeKernel();

        return static::$kernel;
    }

    /**
     * @return Serializer
     */
    protected static function getSerializer()
    {
        return static::getKernel()->getContainer()->get('serializer');
    }

    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/FSCHateoasBundle/');
    }

    protected function tearDown()
    {
        static::$kernel = null;
    }

    protected function assertSerializedXmlEquals($expectedXml, $value)
    {
        $serializedValue = $this->getSerializer()->serialize($value, 'xml');

        $this->assertEquals(sprintf('<?xml version="1.0" encoding="UTF-8"?>%s%s%s', "\n", $expectedXml, "\n"), $serializedValue);
    }

    protected function assertSerializedJsonEquals($expectedSerializedValue, $value)
    {
        $serializedValue = $this->getSerializer()->serialize($value, 'json');

        $this->assertEquals($expectedSerializedValue, $serializedValue);
    }
}
