<?php
declare(strict_types=1);

namespace AbstractDataStructures\Exceptions;


use JetBrains\PhpStorm\Pure;

final class UnableToRetrieveValue extends AbstractDataStructureException
{

    #[Pure] public static function becauseTheStructureIsEmpty(): UnableToRetrieveValue
    {
        return new self('Unable to retrieve values as the structure is empty');
    }
}