<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use Javer\InfluxDB\ODM\Types\TypeEnum;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

final class FieldDescription extends BaseFieldDescription
{
    public function getTargetModel(): ?string
    {
        return null;
    }

    public function isIdentifier(): bool
    {
        return ($this->fieldMapping['type'] ?? null) === TypeEnum::TIMESTAMP;
    }

    public function getValue(object $object): mixed
    {
        return $this->getFieldValue($object, $this->fieldName);
    }

    public function describesSingleValuedAssociation(): bool
    {
        return false;
    }

    public function describesCollectionValuedAssociation(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function setAssociationMapping(array $associationMapping): void
    {
    }

    /**
     * {@inheritDoc}
     */
    protected function setFieldMapping(array $fieldMapping): void
    {
        $this->fieldMapping = $fieldMapping;

        if (!$this->mappingType) {
            $mappingType = $fieldMapping['type'] ?? null;

            if ($mappingType instanceof TypeEnum) {
                $this->mappingType = $mappingType->value;
            }
        }

        $this->fieldName = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     */
    protected function setParentAssociationMappings(array $parentAssociationMappings): void
    {
    }
}
