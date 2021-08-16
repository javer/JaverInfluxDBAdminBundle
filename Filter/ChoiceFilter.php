<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DefaultType;

class ChoiceFilter extends StringFilter
{
    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            DefaultType::class, [
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
            ],
        ];
    }
}
