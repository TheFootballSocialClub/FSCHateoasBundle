<?php

namespace FSC\HateoasBundle\Metadata\Driver;

use Metadata\Driver\AbstractFileDriver;
use Symfony\Component\Yaml\Yaml;

use FSC\HateoasBundle\Metadata\ClassMetadata;

class YamlDriver extends AbstractFileDriver
{
    /**
     * {@inheritdoc}
     */
    protected function loadMetadataFromFile(\ReflectionClass $class, $file)
    {
        $config = Yaml::parse(file_get_contents($file));

        if (!isset($config[$name = $class->getName()])) {
            throw new \RuntimeException(sprintf('Expected metadata for class %s to be defined in %s.', $name, $file));
        }

        $config = $config[$name];

        $classMetadata = new ClassMetadata($name);

        if (isset($config['links'])) {
            $classMetadata->setLinks($this->normalizeLinks($config['links']));
        }

        return $classMetadata;
    }

    protected function normalizeLinks(array $links)
    {
        $newLinks = array();

        foreach ($links as $rel => $link) {
            if (is_array($link) && array_keys($link) === range(0, count($link) - 1)) {
                foreach ($link as $subLink) {
                    $newLinks[] = $this->parseLink($rel, $subLink);
                }

                continue;
            }

            $newLinks[] = $this->parseLink($rel, $link);
        }

        return $newLinks;
    }

    protected function parseLink($rel, $link)
    {
        if (is_string($link)) {
            $link = array(
                'route' => $link,
            );
        }

        return array(
            'rel' => $rel,
            'route' => $link['route'],
            'params' => isset($link['params']) ? $link['params'] : array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'yml';
    }
}
