<?php

declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use j45l\AbstractDataStructures\ImmutableCollection;

final class TestImmutableCollection extends ImmutableCollection
{
    public static function type(): string
    {
        return TestItem::class;
    }
}
