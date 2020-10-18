<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DateRangePickerType;

/**
 * Class DateRangeFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class DateRangeFilter extends AbstractDateFilter
{
    protected bool $range = true;

    /**
     * {@inheritDoc}
     */
    public function getFieldType(): string
    {
        return $this->getOption('field_type', DateRangePickerType::class);
    }
}
