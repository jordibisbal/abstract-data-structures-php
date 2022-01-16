<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use j45l\AbstractDataStructures\Queue;

final class TestQueue extends Queue
{
    public function type(): string
    {
        return TestItem::class;
    }
}
