<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use RuntimeException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\Guesser\TypeGuesserInterface;

/**
 * Class ShowBuilder
 *
 * @package Javer\InfluxDB\AdminBundle\Builder
 */
class ShowBuilder implements ShowBuilderInterface
{
    private TypeGuesserInterface $guesser;

    private array $templates;

    /**
     * ShowBuilder constructor.
     *
     * @param TypeGuesserInterface $guesser
     * @param array                $templates
     */
    public function __construct(TypeGuesserInterface $guesser, array $templates)
    {
        $this->guesser = $guesser;
        $this->templates = $templates;
    }

    /**
     * Returns base list.
     *
     * @param array $options
     *
     * @return FieldDescriptionCollection
     */
    public function getBaseList(array $options = []): FieldDescriptionCollection
    {
        return new FieldDescriptionCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function addField(
        FieldDescriptionCollection $list,
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        AdminInterface $admin
    ): void
    {
        if (null === $type) {
            $guessType = $this->guesser
                ->guessType($admin->getClass(), $fieldDescription->getName(), $admin->getModelManager());

            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($admin, $fieldDescription);

        $admin->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * Fixes field description.
     *
     * @param AdminInterface            $admin
     * @param FieldDescriptionInterface $fieldDescription
     *
     * @throws RuntimeException
     */
    public function fixFieldDescription(AdminInterface $admin, FieldDescriptionInterface $fieldDescription): void
    {
        $fieldDescription->setAdmin($admin);

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            [$metadata, $lastPropertyName, $parentAssociationMappings] = $admin->getModelManager()
                ->getParentMetadataForProperty($admin->getClass(), $fieldDescription->getName());
            $fieldDescription->setParentAssociationMappings($parentAssociationMappings);

            // set the default field mapping
            if (isset($metadata->fieldMappings[$lastPropertyName])) {
                $fieldDescription->setFieldMapping($metadata->fieldMappings[$lastPropertyName]);
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$lastPropertyName])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$lastPropertyName]);
            }
        }

        if (!$fieldDescription->getType()) {
            throw new RuntimeException(
                sprintf('Please define a type for field `%s` in `%s`', $fieldDescription->getName(), get_class($admin))
            );
        }

        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('label', $fieldDescription->getOption('label', $fieldDescription->getName()));

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));
        }
    }

    /**
     * Returns template for the given type.
     *
     * @param string $type
     *
     * @return string|null
     */
    private function getTemplate(string $type): ?string
    {
        return $this->templates[$type] ?? null;
    }
}
