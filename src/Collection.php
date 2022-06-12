<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use Countable;
use j45l\maybe\Maybe\Maybe;
use j45l\maybe\Optional\Optional;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * @template T
 * @extends TypedDictionaryBasedStructure<T>
 * @phpstan-type key int | string | null
 */
#[Immutable] abstract class Collection extends TypedDictionaryBasedStructure implements Countable
{
    #[Pure] public function hasKey(string $key): bool
    {
        return $this->itemsArray->hasKey($key);
    }

    /**
     * @return Optional<T>
     */
    public function get(string $key): Optional
    {
        return $this->itemsArray->offsetGet($key);
    }

    /**
     * @template DT
     * @param DT $default
     * @return T | DT
     */
    public function getOrElse(string $key, mixed $default)
    {
        return $this->itemsArray->offsetGet($key)->getOrElse($default);
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

    /** @param T $value */
    public function append(mixed $value): static
    {
        return match (true) {
            $value instanceof UniqueIndexed => $this->set($value->getUniqueKey(), $value),
            default => $this->unkeyedAppend($value)
        };
    }

    public function foreach(callable $callable): void
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        $this->itemsArray->each($callable);
    }

    /**
     * @return array<T>
     */
    #[Pure] public function values(): array
    {
        return array_values($this->asArray());
    }

    public function sort(callable $comparisonCallable): static
    {
        return new static($this->itemsArray->sort($comparisonCallable));
    }

    /**
     * @phpstan-return array<key>
     */
    #[Pure] public function keys(): array
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

    /**
     * @param T $value
     * @return static
     */
    private function unkeyedAppend(mixed $value): Collection
    {
        $this->guardSet($value);
        return new static($this->itemsArray->append($value));
    }

    public function onHasNotKey(string $key, callable $fn, mixed $default = null): mixed
    {
        return match (true) {
            $this->hasKey($key) => $default,
            default => $fn()
        };
    }
}
