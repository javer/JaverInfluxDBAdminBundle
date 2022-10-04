<?php

namespace Javer\InfluxDB\AdminBundle\Filter;

use Javer\InfluxDB\AdminBundle\Datagrid\ProxyQueryInterface;
use RuntimeException;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use UnexpectedValueException;

class CallbackFilter extends Filter
{
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
    public function getFormOptions(): array
    {
        return [
            'field_type' => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label' => $this->getLabel(),
        ];
    }

    /**
     * @throws RuntimeException
     * @throws UnexpectedValueException
     */
    protected function filter(ProxyQueryInterface $query, string $field, FilterData $data): void
    {
        if (!is_callable($this->getOption('callback'))) {
            throw new RuntimeException(
                sprintf('Please provide a valid callback option "filter" for field "%s"', $this->getName())
            );
        }

        $isActive = call_user_func($this->getOption('callback'), $query, $field, $data);

        if (!is_bool($isActive)) {
            throw new UnexpectedValueException(sprintf(
                'The callback should return a boolean, %s returned',
                is_object($isActive) ? 'instance of "' . get_class($isActive) . '"' : '"' . gettype($isActive) . '"'
            ));
        }

        $this->setActive($isActive);
    }
}
