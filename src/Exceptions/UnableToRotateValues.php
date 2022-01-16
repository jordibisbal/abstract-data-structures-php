<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Exceptions;

use JetBrains\PhpStorm\Pure;

final class UnableToRotateValues extends AbstractDataStructureException
{
    #[Pure] public static function becauseThereIsNotEnoughItemsInTheStructure(int $count): UnableToRotateValues
    {
        return new self(sprintf(
            'Unable to rotate values as three values in the stack are required but there are only %s.',
            $count
        ));
    }
}
