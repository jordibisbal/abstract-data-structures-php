<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use j45l\AbstractDataStructures\Stack;

final class TestStack extends Stack
{
    public function type(): string
    {
        return TestItem::class;
    }
}
