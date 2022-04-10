<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\FailureReasons;

use j45l\maybe\DoTry\Reason;
use JetBrains\PhpStorm\Pure;

final class UnableToRetrieve extends Reason
{
    #[Pure] public static function becauseTheStructureHasNotTheRequestedKey(string $key): self
    {
        return new self(
            sprintf('Unable to retrieve element because the data structure has not the requested key (%s).', $key)
        );
    }
}