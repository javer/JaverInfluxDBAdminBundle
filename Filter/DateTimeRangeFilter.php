<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DateTimeRangePickerType;

/**
 * Class DateTimeRangeFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class DateTimeRangeFilter extends AbstractDateFilter
{
    protected bool $range = true;

    protected bool $time = true;

    /**
     * {@inheritDoc}
     */
    public function getFieldType(): string
    {
        return $this->getOption('field_type', DateTimeRangePickerType::class);
    }
}
