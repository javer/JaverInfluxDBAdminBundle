<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use Javer\InfluxDB\ODM\Types\Type;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

final class TypeGuesser implements TypeGuesserInterface
{
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $fieldMapping = $fieldDescription->getFieldMapping();

        if ([] === $fieldMapping) {
            return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        switch ($fieldDescription->getMappingType()) {
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
