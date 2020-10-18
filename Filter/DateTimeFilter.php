<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\Form\Type\DateTimePickerType;

/**
 * Class DateTimeFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class DateTimeFilter extends AbstractDateFilter
{
    protected bool $time = true;

    /**
     * {@inheritDoc}
     */
    public function getFieldType(): string
    {
        return $this->getOption('field_type', DateTimePickerType::class);
    }
}
