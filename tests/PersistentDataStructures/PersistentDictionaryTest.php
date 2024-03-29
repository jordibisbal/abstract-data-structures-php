<?php

namespace j45l\AbstractDataStructures\Tests\PersistentDataStructures;

use j45l\AbstractDataStructures\PersistentDataStructures\BucketRouter;
use j45l\AbstractDataStructures\PersistentDataStructures\MemoizedBucketRouter;
use j45l\AbstractDataStructures\PersistentDataStructures\PersistentDictionary;
use j45l\Cats\Maybe\None;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;

use function iterator_to_array as iteratorToArray;
use function j45l\Cats\Maybe\None;
use function j45l\Cats\Maybe\Some;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class PersistentDictionaryTest extends TestCase
{
    public function testNewArrayIsEmpty(): void
    {
        $array = PersistentDictionary::fromArray([]);
        assertEquals([], $array->toArray());
    }

    /**
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     * @phpstan-return array<int|string, array<array<int|string, int|string>>>
     */
    public function simpleArraysProvider(): array
    {
        return [
            "Empty" => [[]],
            "123" => [[123]],
            "\"abc\"" => [["abc"]],
            "No key numbers" => [[1, 2, 3]],
            "No key strings" => [["abc", "d", "efg"]],
            "Integer keys" => [[ 1 => 11, 2 => 22, 3 => 33]],
            "String keys" => [[ "a" => "A", "b" => "B", "c" => "C"]],
            "Mixed keys" => [[ 1 => "A", "b" => 22, 3 => "C", "d" => 4, 44 => "E"]]
        ];
    }

    /**
     * @dataProvider simpleArraysProvider
     * @phpstan-param  array<int | string, int | string> $array
     */
    public function testNewArrayFromArrayHasData($array): void
    {
        $persistentDictionary = PersistentDictionary::fromArray($array);

        assertEquals($array, $persistentDictionary->toArray());
        assertCount(count($array), $persistentDictionary);
    }

    /** @dataProvider bucketRouterProvider */
    public function testArrayElementsCanBeRetrieved(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray(['a', 'b' => 'B'], $bucketRouter);

        assertEquals('a', $persistentDictionary[0]->getOrElse(null));
        assertEquals('B', $persistentDictionary['b']->getOrElse(null));
    }

    /** @dataProvider bucketRouterProvider */
    public function testRetrievingAnNonexistentElementReturnsANone(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray(['a', 'b' => 'B'], $bucketRouter);

        $item = $persistentDictionary['c'];

        self::assertInstanceOf(None::class, $item);
    }

    /** @dataProvider bucketRouterProvider */
    public function testModifyingDoesNotChangeOriginal(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3], $bucketRouter);

        $newPersistentDictionary = $persistentDictionary->set(1, 4);

        assertEquals([1, 2, 3], $persistentDictionary->toArray());
        assertEquals([1, 4, 3], $newPersistentDictionary->toArray());
    }

    /**
     * @return array<string, array<int, BucketRouter>>
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function bucketRouterProvider(): array
    {
        return [
            'standardRouter' => [BucketRouter::create(4)],
            'memoizedRouter' => [MemoizedBucketRouter::create(4)],
            'singleBucketRouter' => [new class(4) extends BucketRouter {
                #[Pure] protected function expand(string $index): array
                {
                    return [0, 0, 0, 0];
                }
            }]
        ];
    }

    /** @dataProvider bucketRouterProvider */
    public function testItemsCanBeAdded(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3], $bucketRouter);

        $persistentDictionary = $persistentDictionary->append(4);

        assertEquals([1, 2, 3, 4], $persistentDictionary->toArray());
    }

    /** @dataProvider bucketRouterProvider */
    public function testSettingItemWithIntIndexModifiesNextIndex(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3], $bucketRouter);

        $persistentDictionary = $persistentDictionary->set(5, 5);
        $persistentDictionary = $persistentDictionary->append(4);

        assertEquals([1, 2, 3, 5 => 5, 6 => 4], $persistentDictionary->toArray());
    }

    public function testSettingAKeyAsIntegerIsTheSameAsUsingTheString(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1 => 41]);
        $persistentDictionary =
            $persistentDictionary->set('1', $persistentDictionary->offsetGet('1')->getOrElse(1) + 1);

        assertEquals(42, $persistentDictionary->offsetGet(1)->getOrElse(null));
    }

    public function testGettingANonexistentElementFailRegardingOfTheKeyType(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([]);

        $failureOnInt = $persistentDictionary->offsetGet(42);
        $failureOnString = $persistentDictionary->offsetGet('42');

        assertEquals($failureOnInt, $failureOnString);
    }

    public function testGettingAGeneratorForAllValue(): void
    {
        $array = [1 => 41, 'a' => 42, 2 => '43'];
        assertEquals($array, iteratorToArray(PersistentDictionary::fromArray($array)->yield()));
    }

    public function testCanReturnFirstValue(): void
    {
        assertEquals(Some(42), PersistentDictionary::fromArray([42, 1])->first());
    }

    public function testCanReturnFirstValueWithPredicate(): void
    {
        assertEquals(
            Some(42),
            PersistentDictionary::fromArray([1, 42, 2])->first(fn (int $value): bool => $value === 42)
        );
    }

    public function testCanReturnFirstNoneIfNotFound(): void
    {
        assertEquals(None(), PersistentDictionary::fromArray([])->first());
        assertEquals(
            None(),
            PersistentDictionary::fromArray([])->first(fn (int $value): bool => $value === 42)
        );
        assertEquals(
            None(),
            PersistentDictionary::fromArray([1, 2])->first(fn (int $value): bool => $value === 42)
        );
    }
}
