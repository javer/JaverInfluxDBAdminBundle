<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use Javer\InfluxDB\ODM\Mapping\ClassMetadata;
use Javer\InfluxDB\ODM\MeasurementManager;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

class FieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    private MeasurementManager $measurementManager;

    public function __construct(MeasurementManager $measurementManager)
    {
        $this->measurementManager = $measurementManager;
    }

    /**
     * {@inheritDoc}
     */
    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        [$metadata, $propertyName, $parentAssociationMappings] = $this->getParentMetadataForProperty($class, $name);

        return new FieldDescription(
            $name,
            $options,
            $metadata->fieldMappings[$propertyName] ?? [],
            [],
            $parentAssociationMappings,
            $propertyName
        );
    }

    /**
     * Returns the model's metadata holding the fully qualified property, and the last property name.
     *
     * @param string $baseClass        The base class of the model holding the fully qualified property
     * @param string $propertyFullName The name of the fully qualified property (dot separated property string)
     *
     * @return array{ClassMetadata, string, mixed[]}
     *
     * @phpstan-template T of object
     * @phpstan-param    class-string<T> $baseClass
     * @phpstan-return   array{ClassMetadata<T>, string, mixed[]}
     */
    private function getParentMetadataForProperty(string $baseClass, string $propertyFullName): array
    {
        return [$this->getMetadata($baseClass), $propertyFullName, []];
    }

    /**
     * Returns metadata for the class.
     *
     * @param string $class
     *
     * @return ClassMetadata
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
