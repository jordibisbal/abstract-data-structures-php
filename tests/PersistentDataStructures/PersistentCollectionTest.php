<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\PersistentDataStructures;

use j45l\AbstractDataStructures\Exceptions\UnableToSetValue;
use j45l\AbstractDataStructures\Tests\Stubs\TestItem;
use j45l\AbstractDataStructures\Tests\Stubs\TestPersistentCollection;
use j45l\AbstractDataStructures\Tests\Stubs\UniqueIndexedTestItem;
use j45l\Cats\Maybe\None;
use j45l\Cats\Maybe\Some;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use function iterator_to_array as iteratorToArray;
use function j45l\Cats\Maybe\None;
use function j45l\Cats\Maybe\Some;
use function j45l\functional\map;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

final class PersistentCollectionTest extends testCase
{
    public function testCanBeCreatedEmpty(): void
    {
        assertTrue(TestPersistentCollection::createEmpty()->isEmpty());
    }

    public function testAnItemCanBeAddedToACollection(): void
    {
        $collection = TestPersistentCollection::createEmpty();
        $originalCollection = $collection;
        $newCollection = $collection->append(new TestItem('test'));

        assertFalse($newCollection->isEmpty());
        assertCount(1, $newCollection);
        assertEquals($originalCollection, TestPersistentCollection::createEmpty());
    }

    public function testAnItemCanBeRetrievedByKey(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        $item = $collection->get('b');

        self::assertInstanceOf(Some::class, $item);
        self::assertInstanceOf(TestItem::class, $item->get());
        assertEquals('B', $item->get()->value);
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testWhenRetrievingANonExistingAFailureIsReturned(): void
    {
        $collection = TestPersistentCollection::createEmpty();
        $originalCollection = $collection;

        $failure = $collection->get('b');

        self::assertInstanceOf(None::class, $failure);
        assertEquals($originalCollection, TestPersistentCollection::createEmpty());
    }

    public function testAnItemCanBeSetByKey(): void
    {
        $collection = TestPersistentCollection::createEmpty();
        $originalCollection = $collection;

        $collection = $collection->set('key', new TestItem('value'));

        $item = $collection->get('key');

        self::assertInstanceOf(Some::class, $item);
        self::assertInstanceOf(TestItem::class, $item->get());
        assertEquals('value', $item->get()->value);
        assertEquals($originalCollection, TestPersistentCollection::createEmpty());
    }

    public function testTheValuesCanBeRetrieved(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        assertEquals(array_values($this->anArray()), $collection->values());
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testTheKeysCanBeRetrieved(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        assertEquals(['a', 'b', 'c'], $collection->keys());
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testTheCollectionCanBeSortedByValue(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        $sort = fn ($a, $b) => $b <=> $a;
        $collection = $collection->sort($sort);
        $array = $this->anArray();
        uasort($array, $sort);

        assertEquals(array_values($array), $collection->values());
        assertEquals(array_keys($array), $collection->keys());
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testTheCollectionIsImmutableToSorting(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        $collection->sort(fn ($a, $b) => $b <=> $a);

        assertEquals(array_keys($this->anArray()), $collection->keys());
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testAnItemCanBeRemovedByKey(): void
    {
        $collection = TestPersistentCollection::fromArray($this->aMixedKeysArray());
        $originalCollection = $collection;

        $collection = $collection->remove(1);
        $collection = $collection->remove('b');
        $array = $this->aMixedKeysArray();

        unset($array['b'], $array[1]);

        assertEquals(array_keys($array), $collection->keys());
        assertEquals(array_values($array), $collection->values());
        assertCount(4, $collection);

        assertEquals(TestPersistentCollection::fromArray($this->aMixedKeysArray()), $originalCollection);
    }

    public function testRemovingAnNonexistentKeyProducesNoEffect(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        $collection = $collection->remove('nonexistent');

        assertEquals(array_keys($this->anArray()), $collection->keys());
        assertEquals(array_values($this->anArray()), $collection->values());
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testACollectionCanBeCheckForAKeyExistence(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        assertTrue($collection->hasKey('b'));
        assertFalse($collection->hasKey('d'));
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testAnExceptionIsThrownIfTheAppendedElementIsNotOfTheProperType(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type string but ' .
            'j45l\AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        // @phpstan-ignore-next-line
        TestPersistentCollection::createEmpty()->append('string');
    }

    public function testAnExceptionIsThrownIfTheSetElementIsNotOfTheProperType(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type string but ' .
            'j45l\AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        TestPersistentCollection::createEmpty()->set('', 'string');
    }

    public function testACollectionCanBeRetrievedAsArray(): void
    {
        assertEquals($this->anArray(), TestPersistentCollection::fromArray($this->anArray())->toArray());
    }

    public function testACollectionCanBeRetrievedAsGenerator(): void
    {
        assertEquals($this->anArray(), iteratorToArray(TestPersistentCollection::fromArray($this->anArray())->yield()));
    }

    public function testAnArrayCanBeIterated(): void
    {
        $collectedItems = [];
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        $collection->foreach(
            function (TestItem $item, $key) use (&$collectedItems): void {
                $collectedItems[$key] = $item;
            }
        );

        assertEquals($this->anArray(), $collectedItems);
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    public function testSizeIsCount(): void
    {
        $collection = TestPersistentCollection::fromArray($this->anArray());
        $originalCollection = $collection;

        assertEquals($collection->count(), $collection->size());
        assertEquals($originalCollection, TestPersistentCollection::fromArray($this->anArray()));
    }

    /**
     * This test is really flaky as it depends on implementation details of PersistentCollect, as the sharding
     * mechanism that influences a lot on the memory usage per item
     * Used mainly to tune the sharding algorithm
     */
    public function testMemorySizeIsBigButAddingOneDoesNotDuplicatesMemoryConsumptionForASingleCluster(): void
    {
        $count = 1000;

        $memoryWatermark = $this->getMemoryUse();
        $itemsArray = map(range(1, $count), fn ($int) => new TestItem((string)$int));
        $collection = TestPersistentCollection::fromArray($itemsArray);

        $memoryUsedByCollection = $this->getMemoryUse() - $memoryWatermark;
        $newCollection = $collection->append(new TestItem('new value'));
        $memoryUsedAfterAppendingOne = $this->getMemoryUse() - $memoryWatermark;

        assertCount($count, $collection);
        assertEquals($itemsArray, $collection->toArray());
        assertCount($count + 1, $newCollection);

        $this->assertMemoryIncreaseIsBelowTenPercent($memoryUsedAfterAppendingOne, $memoryUsedByCollection);
    }

    /** @phpstan-return array<int | string, TestItem> */
    #[Pure] private function anArray(): array
    {
        return [
            'a' => new TestItem('A'),
            'b' => new TestItem('B'),
            'c' => new TestItem('C')
         ];
    }

    /** @phpstan-return array<int | string, TestItem> */
    #[Pure] private function aMixedKeysArray(): array
    {
        return [
            'a' => new TestItem('A'),
            'b' => new TestItem('B'),
            'c' => new TestItem('C'),
            0 => new TestItem('0'),
            1 => new TestItem('1'),
            2 => new TestItem('2')
        ];
    }

    /**
     * @return int
     */
    private function getMemoryUse(): int
    {
        gc_collect_cycles();
        return memory_get_usage();
    }

    private function assertMemoryIncreaseIsBelowTenPercent(
        int $memoryUsedAfterAppendingOne,
        int $memoryUsedByCollection
    ): void {
        self::assertLessThan(1.1, $memoryUsedAfterAppendingOne / $memoryUsedByCollection);
    }

    public function testWhenCreatingFromAWrongTypedArrayItFails(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type integer but ' .
            'j45l\AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        // @phpstan-ignore-next-line
        TestPersistentCollection::fromArray([42]);
    }

    public function testAppendingAnElementTwiceAppendItTwice(): void
    {
        $collection = TestPersistentCollection::fromArray([]);
        $collection = $collection->append(TestItem::create('42'));
        $collection = $collection->append(TestItem::create('42'));

        assertCount(2, $collection);
    }

    public function testAppendingAnUniqueElementTwiceAppendItJustOnce(): void
    {
        $collection = TestPersistentCollection::fromArray([]);
        $collection = $collection->append(UniqueIndexedTestItem::create('42'));
        $collection = $collection->append(UniqueIndexedTestItem::create('42'));

        assertCount(1, $collection);
    }

    public function testGetFirst(): void
    {
        $collection = TestPersistentCollection::fromArray([TestItem::create('1'), TestItem::create('2')]);

        assertEquals(Some(TestItem::create('1')), $collection->first());
    }

    public function testGetFirstWithPredicate(): void
    {
        $collection = TestPersistentCollection::fromArray([
            TestItem::create('1'),
            TestItem::create('42'),
            TestItem::create('2')
        ]);

        assertEquals(
            Some(TestItem::create('42')),
            $collection->first(fn (TestItem $item) => $item->value === '42')
        );
    }

    public function testGetFirstOnEmpty(): void
    {
        $collection = TestPersistentCollection::createEmpty();

        assertEquals(None(), $collection->first());
    }

    public function testCanBeMapped(): void
    {
        assertEquals(
            [TestItem::create('42'), TestItem::create('2')],
            TestPersistentCollection::fromArray([TestItem::create('41'), TestItem::create('1')])
                ->map(fn (TestItem $item) => TestItem::create((string) (((int) $item->value) + 1)))
                ->toArray()
        );
    }

    public function testCanBeCreatedFromAMapOperation(): void
    {
        assertEquals(
            [TestItem::create('42'), TestItem::create('2')],
            TestPersistentCollection::fromMap([41, 1], static fn (int $x) => TestItem::create((string) ($x + 1)))
                ->toArray()
        );
    }
}
