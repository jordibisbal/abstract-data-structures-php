<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
declare(strict_types=1);

namespace AbstractDataStructures\Tests;

use AbstractDataStructures\Exceptions\UnableToSetValue;
use AbstractDataStructures\Tests\Stubs\TestCollection;
use AbstractDataStructures\Tests\Stubs\TestItem;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

final class CollectionTest extends testCase
{
    public function testCanBeCreatedEmpty(): void
    {
        assertTrue(TestCollection::createEmpty()->isEmpty());
    }

    public function testFirstAndLastItemsOfAnEmptyCollectionAreNull(): void
    {
        assertNull(TestCollection::createEmpty()->first());
        assertNull(TestCollection::createEmpty()->last());
    }

    public function testAnItemCanBeAddedToACollection(): void
    {
        $collection = TestCollection::createEmpty();
        $newCollection = $collection->append(new TestItem('test'));

        assertFalse($newCollection->isEmpty());
        assertCount(1, $newCollection);
    }

    public function testAnItemCanBeRetrievedFromACollection(): void
    {
        $collection = TestCollection::createEmpty();
        $newCollection = $collection->append(new TestItem('test'));

        assertEquals('test', $newCollection->last()->value);
    }

    public function testAnItemCanBeRetrievedFromAPairOfSiblingCollectionsWithOutInterferingEachOther(): void
    {
        $collection = TestCollection::createEmpty();
        $newCollectionA = $collection->append(new TestItem('test A'));
        $newCollectionB = $collection->append(new TestItem('test B'));

        assertEquals($newCollectionA->first(), $newCollectionA->last());
        assertEquals($newCollectionB->first(), $newCollectionB->last());
        assertEquals('test A', $newCollectionA->last()->value);
        assertEquals('test B', $newCollectionB->last()->value);
    }

    public function testAnItemCanBeRetrievedByKey(): void
    {
        $collection = TestCollection::fromArray($this->anArray());

        assertEquals('B', $collection->get('b')->value);
    }

    public function testAnItemCanBeSetByKey(): void
    {
        $collection = TestCollection::createEmpty();
        $collection = $collection->set('key', new TestItem('value'));

        assertEquals($collection->first(), $collection->last());
        assertEquals('value', $collection->last()->value);
        assertEquals('value', $collection->get('key')->value);
    }

    public function testTheValuesCanBeRetrieved(): void
    {
        $collection = TestCollection::fromArray($this->anArray());

        assertEquals(array_values($this->anArray()), $collection->values());
    }

    public function testTheKeysCanBeRetrieved(): void
    {
        $collection = TestCollection::fromArray($this->anArray());

        assertEquals(['a', 'b', 'c'], $collection->keys());
    }

    public function testTheCollectionCanBeSortedByValue(): void
    {
        $sort = fn($a, $b) => $b <=> $a;

        $collection = TestCollection::fromArray($this->anArray())->sort($sort);

        $array = $this->anArray();
        usort($array, $sort);

        assertEquals(array_values($array), $collection->values());
        assertEquals(array_keys($array), $collection->keys());
    }

    public function testTheCollectionIsImmutableToSorting(): void
    {
        $sort = fn($a, $b) => $b <=> $a;

        $originalCollection = TestCollection::fromArray($this->anArray());
        $originalCollection->sort($sort);

        assertEquals(array_keys($this->anArray()), $originalCollection->keys());
    }

    public function testAnItemCanBeRemovedByKey(): void
    {
        $collection = TestCollection::fromArray($this->anArray());
        $collection = $collection->remove('b');
        $array = $this->anArray();

        unset($array['b']);

        assertEquals(array_keys($array), $collection->keys());
        assertEquals(array_values($array), $collection->values());
    }

    public function testRemovingAnNonexistentKeyProducesNoEffect(): void
    {
        $collection = TestCollection::fromArray($this->anArray());
        $collection = $collection->remove('nonexistent');

        assertEquals(array_keys($this->anArray()), $collection->keys());
        assertEquals(array_values($this->anArray()), $collection->values());
    }

    public function testACollectionCanBeCheckForAKeyExistence(): void
    {
        $collection = TestCollection::fromArray($this->anArray());

        assertTrue($collection->hasKey('b'));
        assertFalse($collection->hasKey('d'));
    }

    public function testAnExceptionIsThrownIfTheSetElementIsNotOfTheProperType(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set values as the given item is of type string but ' .
            'AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        TestCollection::createEmpty()->append('string');
    }

    public function testACollectionCanBeRetrievedAsArray(): void
    {
        assertEquals($this->anArray(), TestCollection::fromArray($this->anArray())->asArray());
    }

    public function testAnArrayCanBeIterated(): void
    {
        $collectedItems = [];
        $collection = TestCollection::fromArray($this->anArray());

        $collection->foreach(
            function (TestItem $item, $key) use (&$collectedItems): void { $collectedItems[$key] = $item; }
        );

        assertEquals($this->anArray(), $collectedItems);
    }

    public function testSizeIsCount(): void
    {
        $collection = TestCollection::fromArray($this->anArray());
        assertEquals($collection->count(), $collection->size());
    }

    #[Pure] private function anArray(): array
    {
        return [
            'a' => new TestItem('A'),
            'b' => new TestItem('B'),
            'c' => new TestItem('C')
         ];
    }
}