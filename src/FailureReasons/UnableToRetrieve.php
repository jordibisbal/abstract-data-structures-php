<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\FailureReasons;

use j45l\maybe\DoTry\Reason;
use JetBrains\PhpStorm\Pure;

final class UnableToRetrieve extends Reason
{
    #[Pure] public static function becauseTheCollectionHasNotTheRequestedKey(string $key): self
    {
        return new self(
            sprintf('Unable to retrieve element because the collection has not he requested key (%s)', $key)
        );
    }
}