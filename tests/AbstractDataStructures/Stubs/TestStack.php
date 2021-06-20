<?php
declare(strict_types=1);

namespace AbstractDataStructures\Tests\Stubs;

use AbstractDataStructures\Stack;

final class TestStack extends Stack
{
    public function type(): string
    {
        return TestItem::class;
    }
}
