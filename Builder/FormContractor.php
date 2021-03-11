<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use RuntimeException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Class FormContractor
 *
 * @package Javer\InfluxDB\AdminBundle\Builder
 */
class FormContractor implements FormContractorInterface
{
    private FormFactoryInterface $formFactory;

    /**
     * FormContractor constructor.
     *
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
        if (!$fieldDescription->getType()) {
            throw new RuntimeException(
                sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin))
            );
        }

        $fieldDescription->setAdmin($admin);
        $fieldDescription->setOption('edit', $fieldDescription->getOption('edit', 'standard'));
    }

    /**
     * {@inheritDoc}
     */
    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, null, $formOptions);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions(
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        array $formOptions = []
    ): array
    {
        return [
            'sonata_field_description' => $fieldDescription,
        ];
    }
}
