<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use Countable;
use j45l\either\Failure;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * @template T
 * @extends TypedDictionaryBasedStructure<T>
 */
#[Immutable] abstract class Collection extends TypedDictionaryBasedStructure implements Countable
{
    #[Pure] public function hasKey(string $key): bool
    {
        return $this->itemsArray->hasKey($key);
    }

    /**
     * @return T | Failure
     */
    public function get(string $key)
    {
        return $this->itemsArray[$key];
    }


    public function set(string $key, mixed $value): static
    {
        $this->guardSet($value);
        return new static($this->itemsArray->set($key, $value));
    }


    public function remove(int | string $key): static
    {
        $new = clone $this;
        $new->itemsArray = $new->itemsArray->unset($key);

        return $new;
    }


    public function append(mixed $value): static
    {
        $this->guardSet($value);
        return new static($this->itemsArray->append($value));
    }

    public function foreach(callable $fn): void
    {
        $this->itemsArray->each($fn);
    }

    /**
     * @return array<T>
     */
    #[Pure] public function values(): array
    {
        return array_values($this->asArray());
    }

    public function sort(callable $sortFn): static
    {
        return new static($this->itemsArray->sort($sortFn));
    }

    public function keys(): array
    {
        return array_keys($this->asArray());
    }

    /**
     * @return array<T>
     */
    #[Pure] public function asArray(): array
    {
        return $this->itemsArray->asArray();
    }


    #[Pure] public function size(): int
    {
        return $this->count();
    }
}
