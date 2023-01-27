<?php

namespace Javer\InfluxDB\AdminBundle\Builder;

use RuntimeException;
use Sonata\AdminBundle\Builder\ShowBuilderInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionCollection;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;

final class ShowBuilder implements ShowBuilderInterface
{
    /**
     * Constructor.
     *
     * @param TypeGuesserInterface $guesser
     * @param string[]             $templates
     */
    public function __construct(
        private readonly TypeGuesserInterface $guesser,
        private readonly array $templates,
    )
    {
    }

    /**
     * {@inheritDoc}
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
        FieldDescriptionInterface $fieldDescription
    ): void
    {
        if ($type === null) {
            $guessType = $this->guesser->guess($fieldDescription);

            assert($guessType !== null);

            $fieldDescription->setType($guessType->getType());
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);

        $fieldDescription->getAdmin()->addShowFieldDescription($fieldDescription->getName(), $fieldDescription);

        $list->add($fieldDescription);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        if (!$fieldDescription->getType()) {
            throw new RuntimeException(
                sprintf(
                    'Please define a type for field `%s` in `%s`',
                    $fieldDescription->getName(),
                    get_class($fieldDescription->getAdmin())
                )
            );
        }

        if (!$fieldDescription->getTemplate()) {
            $fieldDescription->setTemplate($this->getTemplate($fieldDescription->getType()));
        }
    }

    private function getTemplate(string $type): ?string
    {
        return $this->templates[$type] ?? null;
    }
}
