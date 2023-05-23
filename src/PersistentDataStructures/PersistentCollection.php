<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use Closure;
use Countable;
use Generator;
use j45l\AbstractDataStructures\Collection;
use j45l\AbstractDataStructures\TypedDictionaryBasedStructure;
use j45l\AbstractDataStructures\UniqueIndexed;
use j45l\Cats\Maybe\Maybe;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use function j45l\functional\map;

/**
 * @template T
 * @extends TypedDictionaryBasedStructure<T>
 * @phpstan-type key int | string | null
 */
#[Immutable] abstract class PersistentCollection extends TypedDictionaryBasedStructure implements Countable, Collection
{
    #[Pure] public function hasKey(string $key): bool
    {
        return $this->itemsArray->hasKey($key);
    }

    /**
     * @return Maybe<T>
     */
    public function get(string $key): Maybe
    {
        return $this->itemsArray->offsetGet($key);
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

    /**
     * @param Closure(T,string|int=):void $fn
     * @return void
     */
    public function foreach(Closure $fn): void
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        $this->itemsArray->each($fn);
    }

    /**
     * @param Closure(T):T $fn
     * @return static
     */
    public function map(Closure $fn): static
    {
        /** @noinspection PhpExpressionResultUnusedInspection */
        return static::fromArray(map($this->itemsArray->toArray(), $fn));
    }

    /**
     * @return array<T>
     */
    #[Pure] public function values(): array
    {
        return array_values($this->toArray());
    }

    public function sort(Closure $comparisonFn): static
    {
        return new static($this->itemsArray->sort($comparisonFn));
    }

    /**
     * @phpstan-return array<key>
     */
    #[Pure] public function keys(): array
    {
        return array_keys($this->toArray());
    }

    /**
     * @return array<T>
     */
    #[Pure] public function toArray(): array
    {
        return $this->itemsArray->toArray();
    }

    /**
     * @return Generator<T>
     */
    #[Pure] public function yield(): Generator
    {
        return $this->itemsArray->yield();
    }

    #[Pure] public function size(): int
    {
        return $this->count();
    }

    /**
     * @param T $value
     * @return static
     */
    private function unkeyedAppend(mixed $value): PersistentCollection
    {
        $this->guardSet($value);
        return new static($this->itemsArray->append($value));
    }

    public function onHasNotKey(string $key, Closure $fn, mixed $default = null): mixed
    {
        return match (true) {
            $this->hasKey($key) => $default,
            default => $fn()
        };
    }


    /**
     * @param-phpstan callable(T,int|string|null)|null $fn
     * @return Maybe<T>
     */
    public function first(callable $fn = null): Maybe
    {
        $fn ??= (static fn () => true);

        return $this->itemsArray->first($fn);
    }
}
