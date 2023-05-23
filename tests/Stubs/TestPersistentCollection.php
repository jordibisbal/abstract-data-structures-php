<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use j45l\AbstractDataStructures\PersistentDataStructures\PersistentCollection;

/**
 * @extends PersistentCollection<TestItem>
 */
class TestPersistentCollection extends PersistentCollection
{
    public function type(): string
    {
        return TestItem::class;
    }
}
