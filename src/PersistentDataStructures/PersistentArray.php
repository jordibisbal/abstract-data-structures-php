<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use _PHPStan_cbb796380\React\Promise\Exception\LengthException;
use j45l\AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use j45l\AbstractDataStructures\Exceptions\UnableToRotateValues;
use j45l\AbstractDataStructures\Exceptions\UnableToSwapValues;
use JetBrains\PhpStorm\Pure;

use function Functional\each;

/** @template T */
final class PersistentArray
{
    /** @var array<T>  */
    private array $items;

    /** @param array<T> $items */
    private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param array<T> $items
     * @return PersistentArray<T>
     */
    #[Pure] public static function fromArray(array $items): PersistentArray
    {
        return new self($items);
    }

    /** @throws UnableToRetrieveValue */
    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->items)) {
            throw UnableToRetrieveValue::becauseNoSuchKeyExists($key);
        }

        return $this->items[$key];
    }

    /**
     * @param T $value
     * @return PersistentArray<T>
     */
    #[Pure] public function set(string $key, mixed $value): PersistentArray
    {
        $newArray = new self($this->items);
        $newArray->items[$key] = $value;

        return $newArray;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param T $value
     * @return PersistentArray<T>
     */
    #[Pure] public function append(mixed $value): PersistentArray
    {
        $newArray = new self($this->items);

        $newArray->items[] = $value;

        return $newArray;
    }

    /**
     * @throws UnableToRetrieveValue
     */
    public function last(): mixed
    {
        if ($this->count() === 0) {
            throw UnableToRetrieveValue::becauseTheStructureIsEmpty();
        }

        return current(array_slice($this->items, -1, 1));
    }

    /** @throws UnableToRetrieveValue */
    public function peek(int $position): mixed
    {
        if ($position === 0) {
            throw UnableToRetrieveValue::becauseZeroPositionIsInvalid();
        }

        $count = $this->count();
        if ($count < abs($position)) {
            throw UnableToRetrieveValue::becauseNoSuchPositionExists($count, $position);
        }

        return current(array_slice(
            $this->items,
            ($position >= 0) ? ($position - 1) : $position,
            1
        ));
    }

    /**
     * @phpstan-return T
     * @throws UnableToRetrieveValue
     */
    public function first(): mixed
    {
        if ($this->count() === 0) {
            throw UnableToRetrieveValue::becauseTheStructureIsEmpty();
        }

        $result = current(array_slice($this->items, 0, 1));
        if ($result === false) {
            throw new LengthException('Unable to get current element in first subarray');
        }

        return $result;
    }

    public function hasKey(string $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @return PersistentArray<T>
     */
    public function unset(string $key): PersistentArray
    {
        if (!array_key_exists($key, $this->items)) {
            return $this;
        }

        $items = $this->items;
        unset($items[$key]);

        return new self($items);
    }

    public function each(callable $fn): void
    {
        each($this->items, $fn);
    }

    /**
     * @return PersistentArray<T>
     */
    public function sort(callable $sort): PersistentArray
    {
        $newArray = $this->items;
        usort($newArray, $sort);

        return new self($newArray);
    }

    /**
     * @return array<T>
     */
    public function asArray(): array
    {
        return $this->items;
    }

    /**
     * @phpstan-return array<mixed>
     */
    public function pop(): array
    {
        if (count($this->items) === 0) {
            throw UnableToRetrieveValue::becauseTheStructureIsEmpty();
        }

        $newItems = $this->items;
        $item = array_pop($newItems);
        return [new self($newItems), $item];
    }

    /**
     * @param T $item
     * @return PersistentArray<T>
     */
    #[Pure] public function push(mixed $item): PersistentArray
    {
        $newItems = $this->items;
        $newItems[] = $item;

        return new self($newItems);
    }

    /**
     * @param T $item
     * @return PersistentArray<T>
     */
    public function unshift(mixed $item): PersistentArray
    {
        $newItems = $this->items;
        array_unshift($newItems, $item);

        return new self($newItems);
    }

    /**
     * @return PersistentArray<T>
     */
    public function swap(): PersistentArray
    {
        if (count($this->items) < 2) {
            throw UnableToSwapValues::becauseThereIsNotEnoughItemsInTheStructure(count($this->items));
        }

        $newItems = $this->items;

        $newItems[0] = $this->items[1];
        $newItems[1] = $this->items[0];

        return new self($newItems);
    }

    /**
     * @return PersistentArray<T>
     */
    public function rotate(): PersistentArray
    {
        if (count($this->items) < 3) {
            throw UnableToRotateValues::becauseThereIsNotEnoughItemsInTheStructure(count($this->items));
        }

        $newItems = $this->items;

        $newItems[0] = $this->items[1];
        $newItems[1] = $this->items[2];
        $newItems[2] = $this->items[0];

        return new self($newItems);
    }

    /**
     * @return PersistentArray<T>
     */
    public function reverse(): PersistentArray
    {
        return new self(array_reverse($this->asArray()));
    }
}
