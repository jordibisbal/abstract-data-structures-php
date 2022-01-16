<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use j45l\AbstractDataStructures\Collection;

/**
 * @extends Collection<TestItem>
 */
class TestCollection extends Collection
{
    public function type(): string
    {
        return TestItem::class;
    }
}
