<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use Javer\InfluxDB\ODM\Mapping\ClassMetadata;
use Javer\InfluxDB\ODM\MeasurementManager;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    public function __construct(
        private readonly MeasurementManager $measurementManager,
    )
    {
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        return new FieldDescription($name, $options, $this->getMetadata($class)->fieldMappings[$name] ?? []);
    }

    /**
     * Returns metadata for the class.
     *
     * @phpstan-template T of object
     * @phpstan-param    class-string<T> $class
     * @phpstan-return   ClassMetadata<T>
     */
    private function getMetadata(string $class): ClassMetadata
    {
        return $this->measurementManager->getClassMetadata($class);
    }
}
