<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;

/**
 * Class NumberFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class NumberFilter extends Filter
{
    private const CHOICES = [
        NumberOperatorType::TYPE_EQUAL => '=',
        NumberOperatorType::TYPE_GREATER_EQUAL => '>=',
        NumberOperatorType::TYPE_GREATER_THAN => '>',
        NumberOperatorType::TYPE_LESS_EQUAL => '<=',
        NumberOperatorType::TYPE_LESS_THAN => '<',
    ];

    /**
     * {@inheritDoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data): void
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $field = $this->quoteFieldName($field);
        $type = $data['type'] ?? $this->getOption('operator_default_type');
        $operator = $this->getOperator((int) $type);
        $value = $this->quoteFieldValue($data['value']);

        $this->applyWhere($queryBuilder, sprintf("%s %s %s", $field, $operator, $value));
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'operator_type' => NumberOperatorType::class,
            'operator_options' => [],
            'operator_default_type' => NumberOperatorType::TYPE_EQUAL,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            NumberType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
            ],
        ];
    }

    /**
     * Returns operator for the given type.
     *
     * @param integer $type
     *
     * @return string
     */
    private function getOperator(int $type): string
    {
        return self::CHOICES[$type] ?? self::CHOICES[NumberOperatorType::TYPE_EQUAL];
    }
}
