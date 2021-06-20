<?php
declare(strict_types=1);

namespace AbstractDataStructures\Exceptions;

use JetBrains\PhpStorm\Pure;

final class UnableToSwapValues extends AbstractDataStructureException
{
    #[Pure] public static function becauseThereIsNotEnoughItemsInTheStructure(int $count): UnableToSwapValues
    {
        return new self(sprintf(
            'Unable to swap values as two values in the stack are required but there is only %s.',
            $count
        ));
    }
}
