<?php
declare(strict_types=1);

namespace AbstractDataStructures\PersistentDataStructures;


use AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use JetBrains\PhpStorm\Pure;
use function Functional\each;

final class PersistentArray
{
    private array $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    #[Pure] public static function fromArray(array $items): PersistentArray
    {
        return new self($items);
    }

    #[Pure] public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }
    
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

    #[Pure] public function append(mixed $value): PersistentArray
    {
        $newArray = new self($this->items);

        $newArray->items[] = $value;

        return $newArray;
    }

    #[Pure] public function last(): mixed
    {
        if ($this->count() === 0) {
            return null;
        }

        return current(array_slice($this->items, -1, 1));
    }

    public function peek(int $position): mixed
    {
        if ($position === 0) {
            throw UnableToRetrieveValue::becauseZeroPositionIsInvalid();
        }

        $count = $this->count();
        if ($count < abs($position)){
            throw UnableToRetrieveValue::becauseNoSuchPositionExists($count, $position);
        }

        return current(array_slice(
            $this->items,
            ($position > 0) ? ($position - 1) : $position,
            1
        ));
    }

    #[Pure] public function first(): mixed
    {
        if ($this->count() === 0) {
            return null;
        }

        return current(array_slice($this->items, 0, 1));
    }

    public function hasKey(string $offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

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

    public function sort(callable $sort): PersistentArray
    {
        $newArray = $this->items;
        usort($newArray, $sort);

        return new self($newArray);
    }

    public function asArray(): array
    {
        return $this->items;
    }

    public function pop(): array
    {
        if (count($this->items) === 0) {
            throw UnableToRetrieveValue::becauseTheStructureIsEmpty();
        }

        $newItems = $this->items;
        $item = current(array_splice($newItems, count($newItems) - 1, 1));
        return [new self($newItems), $item];
    }

    public function unshift(mixed $item): PersistentArray
    {
        $newItems = $this->items;
        array_unshift($newItems, $item);

        return new self($newItems);
    }
}