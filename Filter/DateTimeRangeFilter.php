<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DateTimeRangePickerType;

class DateTimeRangeFilter extends AbstractDateFilter
{
    protected bool $range = true;

    protected bool $time = true;

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'field_type' => DateTimeRangePickerType::class,
        ]);
    }
}
