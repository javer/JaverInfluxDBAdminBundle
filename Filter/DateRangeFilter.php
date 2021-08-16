<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DateRangePickerType;

class DateRangeFilter extends AbstractDateFilter
{
    protected bool $range = true;

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'field_type' => DateRangePickerType::class,
        ]);
    }
}
