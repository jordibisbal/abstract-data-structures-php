<?php

declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use ArrayAccess;
use Closure;
use Generator;
use j45l\Cats\Maybe\Maybe;
use RuntimeException;

use function array_key_exists as arrayKeyExists;
use function array_keys as arrayKeys;
use function array_push as arrayPush;
use function get_debug_type as getDebugType;
use function j45l\Cats\Maybe\None;
use function j45l\Cats\Maybe\Some;
use function j45l\functional\also;
use function j45l\functional\doEach;
use function j45l\functional\first;
use function j45l\functional\map;
use function j45l\functional\toGenerator;
use function j45l\functional\unindex;
use function j45l\functional\with;

/**
 * @template T
 * @implements Collection<T>
 * @implements ArrayAccess<int|string,T>
 */
abstract class ImmutableCollection implements Collection, ArrayAccess
{
    abstract public static function type(): string;

    /** @param array<T> $items */
    final private function __construct(readonly array $items)
    {
        with(static::type())(
            static fn (string $type) => doEach(
                $items,
                function (mixed $item) use ($type) {
                    if (!is_a($item, $type)) {
                        throw new RuntimeException(
                            sprintf(
                                'Expected all items of <%s>, but a <%s> found.',
                                $type,
                                getDebugType($item)
                            )
                        );
                    }
                }
            )
        );
    }

    /** @param array<T> $items */
    public static function from(array $items): static
    {
        return new static($items);
    }

    public function hasKey(string|int $key): bool
    {
        return arrayKeyExists($key, $this->items);
    }

    public function get(string|int $key): Maybe
    {
        return $this->hasKey($key) ? Some($this->items[$key]) : None();
    }

    public function set(string $key, mixed $value): static
    {
        $items = $this->items;
        $items[$key] = $value;

        return static::from($items);
    }

    public function remove(int | string $key): static
    {
        $items = $this->items;
        unset($items[$key]);

        return static::from($items);
    }

    /** @param T $value */
    public function append(mixed $value): static
    {
        $items = $this->items;
        arrayPush($items, $value);

        return static::from($items);
    }

    /**
     * @param Closure(T,int|string):void $fn
     */
    public function foreach(Closure $fn): void
    {
        foreach ($this->items as $key => $item) {
            $fn($item, $key);
        }
    }

    /**
     * @param Closure(T,string|int): T $fn
     */
    public function map(Closure $fn): static
    {
        return static::fromMap($this->items, $fn);
    }

    /** @return array<T> */
    public function values(): array
    {
        return unindex($this->items);
    }

    /** @param Closure(T,T):int $comparisonFn */
    public function sort(Closure $comparisonFn): static
    {
        $items = $this->items;
        usort($items, $comparisonFn);

        return self::from($items);
    }

    public function keys(): array
    {
        return arrayKeys($this->items);
    }

    /** @return array<T> */
    public function toArray(): array
    {
        return $this->items;
    }

    public function yield(): Generator
    {
        return toGenerator($this->items);
    }

    public function size(): int
    {
        return count($this->items);
    }

    public function onHasNotKey(string $key, Closure $fn, mixed $default = null): mixed
    {
        $default ??= $this;

        return match (true) {
            $this->hasKey($key) => $default,
            default => $fn()
        };
    }

    /** @return Maybe<T> */
    public function first(callable $fn = null): Maybe
    {
        return $this->size() === 0 ? None() : Some(first($this->items));
    }

    /**
     * @param Closure(T,string|int): T $fn
     */
    public static function fromMap(iterable $items, Closure $fn): static
    {
        return self::from(map($items, $fn));
    }

    public function count(): int
    {
        return $this->size();
    }

    /**
     * @param string|int $offset
     * @return T|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this
            ->get($offset)
            ->orElse(also(fn() => trigger_error('Key not found')))
            ->getOrNull();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot modify an immutable collection');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Cannot modify an immutable collection');
    }

    /**
     * @param string|int $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasKey($offset);
    }
}
