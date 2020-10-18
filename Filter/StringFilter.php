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
    public function filter(ProxyQueryInterface $queryBuilder, $name, $field, $data): void
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || $data['value'] === null) {
            return;
        }

        $data['value'] = trim($data['value']);

        if ($data['value'] === '') {
            return;
        }

        $type = $data['type'] ?? StringOperatorType::TYPE_CONTAINS;
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

        $this->applyWhere($queryBuilder, sprintf('%s %s ' . $format, $field, $operator, $value));
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
                'operator_type' => StringOperatorType::class,
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
        return self::CHOICES[$type] ?? self::CHOICES[StringOperatorType::TYPE_EQUAL];
    }
}
