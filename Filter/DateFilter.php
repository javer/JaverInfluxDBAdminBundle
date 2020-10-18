<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DatePickerType;

/**
 * Class DateFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class DateFilter extends AbstractDateFilter
{
    /**
     * {@inheritDoc}
     */
    public function getFieldType(): string
    {
        return $this->getOption('field_type', DatePickerType::class);
    }
}
