<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Form\Type\Operator\StringOperatorType;

/**
 * Class StringFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class StringFilter extends Filter
{
    private const CHOICES = [
        StringOperatorType::TYPE_CONTAINS => '=~',
        StringOperatorType::TYPE_STARTS_WITH => '=~',
        StringOperatorType::TYPE_ENDS_WITH => '=~',
        StringOperatorType::TYPE_NOT_CONTAINS => '!~',
        StringOperatorType::TYPE_EQUAL => '=',
    ];

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'operator_type' => StringOperatorType::class,
            'operator_options' => [],
            'operator_default_type' => StringOperatorType::TYPE_CONTAINS,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderSettings(): array
    {
        return [
            ChoiceType::class,
            [
                'field_type' => $this->getFieldType(),
                'field_options' => $this->getFieldOptions(),
                'label' => $this->getLabel(),
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function filter(ProxyQueryInterface $query, string $field, $data): void
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || $data['value'] === null) {
            return;
        }

        $data['value'] = trim($data['value']);

        if ($data['value'] === '') {
            return;
        }

        $field = $this->quoteFieldName($field);
        $type = $data['type'] ?? $this->getOption('operator_default_type');
        $operator = $this->getOperator((int) $type);
        $format = "'%s'";

        if (in_array($type, [StringOperatorType::TYPE_CONTAINS, StringOperatorType::TYPE_NOT_CONTAINS], true)) {
            $format = '/%s/';
        } elseif ($type === StringOperatorType::TYPE_STARTS_WITH) {
            $format = '/^%s/';
        } elseif ($type === StringOperatorType::TYPE_ENDS_WITH) {
            $format = '/%s$/';
        }

        $value = $type === StringOperatorType::TYPE_EQUAL ? $data['value'] : preg_quote($data['value'], '/');

        $this->applyWhere($query, sprintf('%s %s ' . $format, $field, $operator, $value));
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
        return self::CHOICES[$type] ?? self::CHOICES[StringOperatorType::TYPE_EQUAL];
    }
}
