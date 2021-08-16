<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use RuntimeException;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

final class FieldDescription extends BaseFieldDescription
{
    public function getTargetModel(): ?string
    {
        return null;
    }

    public function isIdentifier(): bool
    {
        return $this->fieldMapping['id'] ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(object $object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

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
        $this->associationMapping = $associationMapping;

        $this->mappingType = $this->mappingType ?: $associationMapping['type'];
        $this->fieldName = $associationMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     */
    protected function setFieldMapping(array $fieldMapping): void
    {
        $this->fieldMapping = $fieldMapping;

        $this->mappingType = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    protected function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!is_array($parentAssociationMapping)) {
                throw new RuntimeException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }
}
