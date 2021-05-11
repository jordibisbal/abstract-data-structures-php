<?php
declare(strict_types=1);

namespace AbstractDataStructures\Exceptions;


use JetBrains\PhpStorm\Pure;

final class UnableToSetValue extends AbstractDataStructureException
{

    #[Pure] public static function becauseTheItemIsNotOfTheProperType(mixed $item, string $type): UnableToSetValue
    {
        return new self(sprintf(
            'Unable to set values as the given item is of type %s but %s expected.',
            is_object($item) ? get_class($item) : gettype($item),
            $type
        ));
    }
}