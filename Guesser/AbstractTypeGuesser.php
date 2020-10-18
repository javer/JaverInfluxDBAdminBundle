<?php

namespace Javer\InfluxDB\AdminBundle\Guesser;

use Javer\InfluxDB\ODM\Mapping\MappingException;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * Class AbstractTypeGuesser
 *
 * @package Javer\InfluxDB\AdminBundle\Guesser
 */
abstract class AbstractTypeGuesser implements TypeGuesserInterface
{
    /**
     * Returns etParentMetadataForProperty.
     *
     * @param string                $baseClass
     * @param string                $propertyFullName
     * @param ModelManagerInterface $modelManager
     *
     * @return array|null
     */
    protected function getParentMetadataForProperty(
        string $baseClass,
        string $propertyFullName,
        ModelManagerInterface $modelManager
    ): ?array
    {
        try {
            return $modelManager->getParentMetadataForProperty($baseClass, $propertyFullName);
        } catch (MappingException $e) {
            // no metadata found.
            return null;
        }
    }
}
