<?php

namespace j45l\AbstractDataStructures\Tests;

use j45l\AbstractDataStructures\PersistentDataStructures\BucketRouter;
use j45l\AbstractDataStructures\PersistentDataStructures\MemoizedBucketRouter;
use j45l\AbstractDataStructures\PersistentDataStructures\PersistentDictionary;
use j45l\maybe\DoTry\Failure;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

class PersistentDictionaryTest extends TestCase
{
    public function testNewArrayIsEmpty(): void
    {
        $array = PersistentDictionary::fromArray([]);
        assertEquals([], $array->asArray());
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

        assertEquals($array, $persistentDictionary->asArray());
        assertCount(count($array), $persistentDictionary);
    }

    /** @dataProvider bucketRouterProvider */
    public function testArrayElementsCanBeRetrieved(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray(['a', 'b' => 'B'], $bucketRouter);

        assertEquals('a', $persistentDictionary[0]);
        assertEquals('B', $persistentDictionary['b']);
    }

    /** @dataProvider bucketRouterProvider */
    public function testRetrievingAnNonexistentElementReturnsAFailure(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray(['a', 'b' => 'B'], $bucketRouter);

        $item = $persistentDictionary['c'];

        self::assertInstanceOf(Failure::class, $item);
        assertEquals('Element with index [c] does not exist.', $item->reason()->toString());
    }

    /** @dataProvider bucketRouterProvider */
    public function testModifyingDoesNotChangeOriginal(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3], $bucketRouter);

        $newPersistentDictionary = $persistentDictionary->set(1, 4);

        assertEquals([1, 2, 3], $persistentDictionary->asArray());
        assertEquals([1, 4, 3], $newPersistentDictionary->asArray());
    }

    /**
     * @return array<string, array<int, BucketRouter>>
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function bucketRouterProvider(): array
    {
        return [
            'standardRouter' => [new BucketRouter(4)],
            'memoizedRouter' => [new MemoizedBucketRouter(4)],
            'singleBucketRouter' => [new class(4) extends BucketRouter {
                protected function expand(string $index): array
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

        assertEquals([1, 2, 3, 4], $persistentDictionary->asArray());
    }

    /** @dataProvider bucketRouterProvider */
    public function testSettingItemWithIntIndexModifiesNextIndex(BucketRouter $bucketRouter): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3], $bucketRouter);

        $persistentDictionary = $persistentDictionary->set(5, 5);
        $persistentDictionary = $persistentDictionary->append(4);

        assertEquals([1, 2, 3, 5 => 5, 6 => 4], $persistentDictionary->asArray());
    }
}
