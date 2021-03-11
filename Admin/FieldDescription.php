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
     */
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

        return $this->getFieldValue($object, $this->getFieldName());
    }

    /**
     * {@inheritDoc}
     */
    protected function setAssociationMapping(array $associationMapping): void
    {
        $this->associationMapping = $associationMapping;

        $this->type = $this->type ?: (string) $associationMapping['type'];
        $this->mappingType = $this->mappingType ?: $associationMapping['type'];
    }

    /**
     * {@inheritDoc}
     */
    protected function setFieldMapping(array $fieldMapping): void
    {
        $this->fieldMapping = $fieldMapping;

        $this->type = $this->type ?: (string) $fieldMapping['type'];
        $this->mappingType = $this->mappingType ?: $fieldMapping['type'];
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
