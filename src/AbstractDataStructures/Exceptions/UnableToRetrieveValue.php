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
        return new self('Because zero position is invalid.');
    }

    #[Pure] public static function becauseNoSuchPositionExists($count, $position): UnableToRetrieveValue
    {
        return new self(
            sprintf("Because no such position exists, asked for %s but only %s available.", $position, $count)
        );
    }
}