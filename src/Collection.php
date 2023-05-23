<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use Closure;
use Generator;
use j45l\AbstractDataStructures\Exceptions\UnableToSetValue;
use j45l\Cats\Maybe\Maybe;

/**
 * @template T
 * @phpstan-type key int | string | null
 */
interface Collection
{
    public function hasKey(string $key): bool;

    /**
     * @return Maybe<T>
     */
    public function get(string $key): Maybe;

    public function set(string $key, mixed $value): static;

    public function remove(int | string $key): static;

    /** @param T $value */
    public function append(mixed $value): static;

    /**
     * @param Closure(T,string|int=):void $fn
     * @return void
     */
    public function foreach(Closure $fn): void;

    /**
     * @param Closure(T):T $fn
     * @return static
     */
    public function map(Closure $fn): static;

    /**
     * @return array<T>
     */
    public function values(): array;

    public function sort(Closure $comparisonFn): static;

    /**
     * @phpstan-return array<key>
     */
    public function keys(): array;

    /**
     * @return array<T>
     */
    public function toArray(): array;

    /**
     * @return Generator<T>
     */
    public function yield(): Generator;

    public function size(): int;

    public function onHasNotKey(string $key, Closure $fn, mixed $default = null): mixed;

    /**
     * @param-phpstan callable(T,int|string|null)|null $fn
     * @return Maybe<T>
     */
    public function first(callable $fn = null): Maybe;

    /**
     * @param Closure(mixed):T $fn
     * @param iterable<mixed> $items
     * @throws UnableToSetValue
     */
    public static function fromMap(iterable $items, Closure $fn): static;

    public function count(): int;
}
