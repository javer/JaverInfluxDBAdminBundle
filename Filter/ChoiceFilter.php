<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

class ChoiceFilter extends StringFilter
{
    /**
     * {@inheritDoc}
     */
    public function getFormOptions(): array
    {
        return [
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label' => $this->getLabel(),
        ];
    }
}
