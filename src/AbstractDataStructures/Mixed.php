<?php
declare(strict_types=1);

namespace AbstractDataStructures;

use JetBrains\PhpStorm\Pure;

final class Mixed
{
    private function __construct(private mixed $value) {}

    #[Pure] public function from(mixed $value): self
    {
        return new self($value);
    }

    public function value(): mixed {
        return $this->value;
    }
}