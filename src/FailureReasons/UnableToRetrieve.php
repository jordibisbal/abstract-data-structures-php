<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\FailureReasons;

use j45l\Cats\Either\Reason\Reason;
use JetBrains\PhpStorm\Pure;

final class UnableToRetrieve implements Reason
{
    #[Pure] private function __construct(private readonly string $reason)
    {
    }

    #[Pure] public static function becauseTheStructureHasNotTheRequestedKey(string $key): self
    {
        return new self(
            sprintf('Unable to retrieve element because the data structure has not the requested key (%s).', $key)
        );
    }

    #[Pure] public function toString(): string
    {
        return (string) $this;
    }

    #[Pure]  public function __toString(): string
    {
        return $this->reason;
    }

    public function reason(): string
    {
        return (string) $this;
    }
}
