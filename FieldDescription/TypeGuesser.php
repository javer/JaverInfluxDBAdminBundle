<?php

namespace Javer\InfluxDB\AdminBundle\FieldDescription;

use Javer\InfluxDB\ODM\Types\TypeEnum;
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

        return match (TypeEnum::tryFrom($fieldDescription->getMappingType())) {
            TypeEnum::TIMESTAMP => new TypeGuess(FieldDescriptionInterface::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE),
            TypeEnum::BOOLEAN => new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE),
            TypeEnum::INTEGER => new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::HIGH_CONFIDENCE),
            TypeEnum::FLOAT => new TypeGuess(FieldDescriptionInterface::TYPE_FLOAT, [], Guess::HIGH_CONFIDENCE),
            TypeEnum::STRING => new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE),
            default => new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE),
        };
    }
}
