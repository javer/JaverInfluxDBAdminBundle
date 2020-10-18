<?php

namespace Javer\InfluxDB\AdminBundle\Guesser;

use Javer\InfluxDB\ODM\Types\Type;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistry;
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
    public function guessType($class, $property, ModelManagerInterface $modelManager): TypeGuess
    {
        if (!$parentMetadata = $this->getParentMetadataForProperty($class, $property, $modelManager)) {
            return new TypeGuess(TemplateRegistry::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }

        [$metadata, $propertyName] = $parentMetadata;

        switch ($metadata->getTypeOfField($propertyName)) {
            case Type::TIMESTAMP:
                return new TypeGuess(TemplateRegistry::TYPE_DATETIME, [], Guess::HIGH_CONFIDENCE);
            case Type::BOOLEAN:
                return new TypeGuess(TemplateRegistry::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case Type::INTEGER:
                return new TypeGuess(TemplateRegistry::TYPE_INTEGER, [], Guess::HIGH_CONFIDENCE);
            case Type::FLOAT:
                return new TypeGuess(TemplateRegistry::TYPE_FLOAT, [], Guess::HIGH_CONFIDENCE);
            case Type::STRING:
                return new TypeGuess(TemplateRegistry::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            default:
                return new TypeGuess(TemplateRegistry::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
        }
    }
}
