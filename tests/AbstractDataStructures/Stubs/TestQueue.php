<?php
declare(strict_types=1);

namespace AbstractDataStructures\Tests\Stubs;


use AbstractDataStructures\Queue;

final class TestQueue extends Queue
{
    public function type(): string
    {
        return TestItem::class;
    }
}