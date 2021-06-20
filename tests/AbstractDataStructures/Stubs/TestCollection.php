<?php
declare(strict_types=1);

namespace AbstractDataStructures\Tests\Stubs;

use AbstractDataStructures\Collection;

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
