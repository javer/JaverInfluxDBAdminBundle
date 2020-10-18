<?php

namespace Javer\InfluxDB\AdminBundle\Admin;

use RuntimeException;
use Sonata\AdminBundle\Admin\BaseFieldDescription;

/**
 * Class FieldDescription
 *
 * @package Javer\InfluxDB\AdminBundle\Admin
 */
class FieldDescription extends BaseFieldDescription
{
    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function setAssociationMapping($associationMapping): void
    {
        if (!is_array($associationMapping)) {
            throw new RuntimeException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;

        $this->type = $this->type ?: $associationMapping['type'];
        $this->mappingType = $this->mappingType ?: $associationMapping['type'];
        $this->fieldName = $associationMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetEntity(): ?string
    {
        return $this->getTargetModel();
    }

    /**
     * Returns target model.
     *
     * @return string|null
     */
    public function getTargetModel(): ?string
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function setFieldMapping($fieldMapping): void
    {
        if (!is_array($fieldMapping)) {
            throw new RuntimeException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type = $this->type ?: $fieldMapping['type'];
        $this->mappingType = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!is_array($parentAssociationMapping)) {
                throw new RuntimeException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier(): bool
    {
        return $this->fieldMapping['id'] ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

        return $this->getFieldValue($object, $this->fieldName);
    }
}
