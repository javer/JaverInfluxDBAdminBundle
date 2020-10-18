<?php

namespace Javer\InfluxDB\AdminBundle\Model;

use LogicException;

/**
 * Class MissingPropertyMetadataException
 *
 * @package Javer\InfluxDB\AdminBundle\Model
 */
final class MissingPropertyMetadataException extends LogicException
{
    /**
     * MissingPropertyMetadataException constructor.
     *
     * @param string $class
     * @param string $property
     */
    public function __construct(string $class, string $property)
    {
        parent::__construct(sprintf(
            'No metadata found for property `%s::$%s`. Please make sure your InfluxDB mapping is properly configured.',
            $class,
            $property
        ));
    }
}
