<?php
declare(strict_types=1);

namespace AbstractDataStructures\Exceptions;

final class UnableToSetValue extends AbstractDataStructureException
{
    public static function becauseTheItemIsNotOfTheProperType(mixed $item, string $type): UnableToSetValue
    {
        return new self(sprintf(
            'Unable to set value as the given item is of type %s but %s expected.',
            is_object($item) ? get_class($item) : gettype($item),
            $type
        ));
    }
}
