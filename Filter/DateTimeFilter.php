<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DateTimePickerType;

class DateTimeFilter extends AbstractDateFilter
{
    protected bool $time = true;

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'field_type' => DateTimePickerType::class,
        ]);
    }
}
