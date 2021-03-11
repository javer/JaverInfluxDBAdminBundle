<?php

namespace Javer\InfluxDB\AdminBundle\Guesser;

use Javer\InfluxDB\ODM\Types\Type;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * Class TypeGuesser
 *
 * @package Javer\InfluxDB\AdminBundle\Guesser
 */
class TypeGuesser extends AbstractTypeGuesser
{
    /**
     * {@inheritDoc}
     */
    public function guessType(string $class, string $property, ModelManagerInterface $modelManager): ?TypeGuess
    {
        if (!$parentMetadata = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        [$metadata, $propertyName] = $parentMetadata;

        switch ($metadata->getTypeOfField($propertyName)) {
            case Type::TIMESTAMP:
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::BOOLEAN:
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case Type::INTEGER:
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::HIGH_CONFIDENCE);
            case Type::FLOAT:
                return new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::HIGH_CONFIDENCE);
            case Type::STRING:
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }
    }
}
