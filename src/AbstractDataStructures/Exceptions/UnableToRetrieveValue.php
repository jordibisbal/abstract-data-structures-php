<?php
declare(strict_types=1);

namespace AbstractDataStructures\Exceptions;


use JetBrains\PhpStorm\Pure;

final class UnableToRetrieveValue extends AbstractDataStructureException
{
    #[Pure] public static function becauseTheStructureIsEmpty(): UnableToRetrieveValue
    {
        return new self('Unable to retrieve values as the structure is empty.');
    }

    #[Pure] public static function becauseZeroPositionIsInvalid(): UnableToRetrieveValue
    {
        return new self('Zero position is invalid.');
    }

    #[Pure] public static function becauseNoSuchPositionExists($count, $position): UnableToRetrieveValue
    {
        return new self(
            sprintf("No such position exists, asked for %s but only %s available.", $position, $count)
        );
    }

    #[Pure] public static function becauseNoSuchKeyExists(string $key): UnableToRetrieveValue
    {
        return new self(
            sprintf("No such key exists (%s).", $key)
        );
    }
}