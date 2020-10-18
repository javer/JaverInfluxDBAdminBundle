<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class BooleanFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class BooleanFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data): void
    {
        if (
            !$data
            || !is_array($data)
            || !array_key_exists('type', $data)
            || !array_key_exists('value', $data)
            || !in_array($data['value'], [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)
        ) {
            return;
        }

        $this->applyWhere($queryBuilder, sprintf('%s = %d', $field, $data['value'] === BooleanType::TYPE_YES ? 1 : 0));
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            DefaultType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'operator_type' => HiddenType::class,
                'operator_options' => [],
                'label' => $this->getLabel(),
            ],
        ];
    }
}
