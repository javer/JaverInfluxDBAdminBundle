<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DatePickerType;

class DateFilter extends AbstractDateFilter
{
    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'field_type' => DatePickerType::class,
        ]);
    }
}
