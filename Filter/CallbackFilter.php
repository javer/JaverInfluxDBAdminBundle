<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use RuntimeException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class CallbackFilter
 *
 * @package Javer\InfluxDB\AdminBundle\Filter
 */
class CallbackFilter extends Filter
{
    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data): void
    {
        if (!is_callable($this->getOption('callback'))) {
            throw new RuntimeException(
                sprintf('Please provide a valid callback option "filter" for field "%s"', $this->getName())
            );
        }

        $this->active = call_user_func($this->getOption('callback'), $queryBuilder, $alias, $field, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'callback' => null,
            'field_type' => TextType::class,
            'operator_type' => HiddenType::class,
            'operator_options' => [],
        ];
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
                'operator_type' => $this->getOption('operator_type'),
                'operator_options' => $this->getOption('operator_options'),
                'label' => $this->getLabel(),
            ],
        ];
    }
}
