<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests;

use j45l\AbstractDataStructures\Tests\Stubs\TestImmutableCollection;
use j45l\AbstractDataStructures\Tests\Stubs\TestItem;
use PHPUnit\Framework\TestCase;

use RuntimeException;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class ImmutableCollectionTest extends TestCase
{
    public function testCanGetType(): void
    {
        assertEquals(TestItem::class, TestImmutableCollection::type());
    }

    public function testToArray(): void
    {
        assertEquals(
            ['a' => TestItem::create('A'), 'b' => TestItem::create('B')],
            $this->immutableCollection()->toArray()
        );
    }

    public function testArrayAccessGet(): void
    {
        assertEquals(
            TestItem::create('A'),
            $this->immutableCollection()['a']
        );
    }

    public function testArrayAccessHas(): void
    {
        assertTrue($this->immutableCollection()->offsetExists('a'));
        assertFalse($this->immutableCollection()->offsetExists('A'));
    }

    public function testThrowsWhenConstructingWithWrongTypes(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Expected all items of <j45l\AbstractDataStructures\Tests\Stubs\TestItem>, but a <int> found.'
        );

        TestImmutableCollection::from([1]);
    }

    public function immutableCollection(): TestImmutableCollection
    {
        return TestImmutableCollection::from(['a' => TestItem::create('A'), 'b' => TestItem::create('B')]);
    }
}
