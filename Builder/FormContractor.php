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
        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->fieldMappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$fieldDescription->getName()]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

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
    public function getFormBuilder($name, array $options = []): FormBuilderInterface
    {
        return $this->formFactory->createNamedBuilder($name, FormType::class, null, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription): array
    {
        return [
            'sonata_field_description' => $fieldDescription,
        ];
    }
}
