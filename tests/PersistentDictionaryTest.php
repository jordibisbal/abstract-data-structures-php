<?php

namespace j45l\AbstractDataStructures\Tests;

use j45l\AbstractDataStructures\PersistentDataStructures\PersistentDictionary;
use j45l\either\Failure;
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
     * @return array<array>
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

    public function testArrayElementsCanBeRetrieved(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray(['a', 'b' => 'B']);

        assertEquals('a', $persistentDictionary[0]);
        assertEquals('B', $persistentDictionary['b']);
    }

    public function testRetrievingAnNonexistentElementReturnsAFailure(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray(['a', 'b' => 'B']);

        $item = $persistentDictionary['c'];

        self::assertInstanceOf(Failure::class, $item);
        assertEquals('Element with index [c] does not exist.', $item->reason()->asString());
    }

    public function testModifyingDoesNotChangeOriginal(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3]);

        $newPersistentDictionary = $persistentDictionary->set(1, 4);

        assertEquals([1, 2, 3], $persistentDictionary->asArray());
        assertEquals([1, 4, 3], $newPersistentDictionary->asArray());
    }

    public function testItemsCanBeAdded(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3]);

        $persistentDictionary = $persistentDictionary->append(4);

        assertEquals([1, 2, 3, 4], $persistentDictionary->asArray());
    }

    public function testSettingItemWithIntIndexModifiesNextIndex(): void
    {
        $persistentDictionary = PersistentDictionary::fromArray([1, 2, 3]);

        $persistentDictionary = $persistentDictionary->set(5, 5);
        $persistentDictionary = $persistentDictionary->append(4);

        assertEquals([1, 2, 3, 5 => 5, 6 => 4], $persistentDictionary->asArray());
    }
}
