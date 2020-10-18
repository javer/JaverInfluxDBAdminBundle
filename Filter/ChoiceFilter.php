<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;

/**
 * Class ChoiceFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class ChoiceFilter extends StringFilter
{
    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            DefaultType::class, [
                'operator_type' => ChoiceType::class,
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
            ],
        ];
    }
}
