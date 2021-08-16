<?php

namespace Javer\InfluxDB\AdminBundle\Model;

use LogicException;

final class MissingPropertyMetadataException extends LogicException
{
    public function __construct(string $class, string $property)
    {
        parent::__construct(sprintf(
            'No metadata found for property `%s::$%s`. Please make sure your InfluxDB mapping is properly configured.',
            $class,
            $property
        ));
    }
}
