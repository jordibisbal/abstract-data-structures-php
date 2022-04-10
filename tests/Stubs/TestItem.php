<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use JetBrains\PhpStorm\Pure;

class TestItem
{
    final public function __construct(public string $value)
    {
    }

    #[Pure] public static function create(string $value): static
    {
        return new static($value);
    }
}
