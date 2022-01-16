<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use ArrayAccess;
use Countable;
use j45l\either\Failure;
use j45l\either\None;
use j45l\either\Reason;
use JetBrains\PhpStorm\Pure;
use function Functional\each;

/**
 * @template T
 * @implements ArrayAccess<int | string, T>
 * @phpstan-type key int | string | null
 */
final class PersistentDictionary implements Countable, ArrayAccess
{
    private const SHARD_DEPTH = 4;

    /** @var array<key, mixed> */
    private array $nodes;

    private int $nextIndex;

    private int $count;

    /** @var key */
    private int | string | null $first;

    /** @var key  */
    private int | string | null $last;

    /**
     * @param array<T> $items
     */
    private function __construct(array $items)
    {
        $this->nodes = [];
        $this->first = null;
        $this->last = null;
        $this->count = 0;
        $this->nextIndex = 0;

        each($items, fn ($value, $key) => $this->setNode($key, $value));
    }

    /**
     * @param array<T> $items
     * @return PersistentDictionary<T>
     * @phpstan-pure
     */
    public static function fromArray(array $items): PersistentDictionary
    {
        return new self($items);
    }

    /**
     * @param T $value
     * @return PersistentDictionary<T>
     */
    public function set(int | string $key, mixed $value): PersistentDictionary
    {
        $newArray = clone $this;
        $newArray->setNode($key, $value);

        return $newArray;
    }

    public function count(): int
    {
        return $this->count;
    }

    /**
     * @return PersistentDictionary<T>
     * @phpstan-pure
     */
    public function append(mixed $value): PersistentDictionary
    {
        $newArray = clone $this;
        $newArray->setNode($newArray->getNextIndex(), $value);

        return $newArray;
    }

    /** @phpstan-pure */
    public function last(): mixed
    {
        if (!$this->last) {
            return None::create();
        }

        return $this->offsetGet($this->last);
    }

    /** @phpstan-pure */
    public function first(): mixed
    {
        if (!$this->first) {
            return None::create();
        }

        return $this->offsetGet($this->first);
    }

    public function hasKey(int | string $offset): bool
    {
        return array_key_exists($offset, $this->getLeafArray($offset));
    }

    /**
     * @return PersistentDictionary<T>
     */
    public function unset(int | string $key): PersistentDictionary
    {
        if (!$this->hasKey($key)) {
            return $this;
        }

        $new = clone $this;
        $new->count--;

        $nodes = &$new->createLeafArray($key);

        $new->setPriorNext($new, $key);
        $new->unsetNode($nodes, $key);

        return $new;
    }

    public function each(callable $fn): void
    {
        $key = $this->first;
        while ($key !== null) {
            $node = $this->getNode($key);
            $fn($node->value(), $key);
            $key = $node->next();
        }
    }

    /** @return PersistentDictionary<T> */
    public function sort(callable $sort): PersistentDictionary
    {
        $newArray = $this->asArray();
        uasort($newArray, $sort);

        return new self($newArray);
    }

    /** @return array<T> */
    public function asArray(): array
    {
        $collected = [];
        $this->each(function ($item, $key) use (&$collected) {
            $collected[$key] = $item;
        });

        return $collected;
    }

    /**
     * @return array<string>
     */
    private function getLeafPath(int | string $index): array
    {
        return str_split(substr((md5((string) $index)), 1, self::SHARD_DEPTH));
    }

    /**
     * @phpstan-impure
     * @phpstan-return array<key, mixed>
     * @noinspection PhpPureAttributeCanBeAddedInspection
     */
    private function &createLeafArray(mixed $index): array
    {
        $items = &$this->nodes;

        foreach ($this->getLeafPath($index) as $branch) {
            if (!array_key_exists($branch, $items)) {
                $items[$branch] = [];
            }
            $items = &$items[$branch];
        }

        return $items;
    }

    /**
     * @return array<Node>
     * @noinspection PhpPureAttributeCanBeAddedInspection
     */
    private function &getLeafArray(mixed $index): array
    {
        $items = &$this->nodes;

        foreach ($this->getLeafPath($index) as $branch) {
            if (!array_key_exists($branch, $items)) {
                $array = [];
                return $array;
            }
            $items = &$items[$branch];
        }

        return $items;
    }

    /**
     * @param int | string  $offset
     */
    #[Pure] public function offsetExists(mixed $offset): bool
    {
        /** @var array<T> $items */
        $items = &$this->nodes;

        foreach ($this->getLeafPath($offset) as $branch) {
            if (!array_key_exists($branch, $items)) {
                return false;
            }
            $items = &$items[$branch];
        }

        return array_key_exists($offset, $items);
    }

    /**
     * @param int | string $offset
     * @phpstan-return Node
     */
    private function getNode(int | string $offset): mixed
    {
        return $this->getLeafArray($offset)[$offset];
    }

    /**
     * @param int | string $offset
     * @phpstan-return T | Failure
     */
    public function offsetGet($offset): mixed
    {
        if (!array_key_exists($offset, $this->getLeafArray($offset))) {
            return Failure::from(
                Reason::from(sprintf('Element with index [%s] does not exist.', (string) $offset))
            );
        }

        return $this->getLeafArray($offset)[$offset]->value();
    }

    private function setNode(mixed $key, mixed $value): void
    {
        $nodes = &$this->createLeafArray($key);

        $this->updateNextIndex($key);

        if (array_key_exists($key, $nodes) && ($nodes[$key] === $value)) {
            return;
        }

        $newArray = $nodes;
        $node = new Node(null, null, $value);

        if (!array_key_exists($key, $newArray)) {
            $this->count++;
            $node = new Node($this->last, null, $value);

            $this->updateFirstWhenAdding($this, $key);
            $this->updateLastWhenAdding($this, $key);
        }

        $newArray[$key] = $node;
        $nodes = $newArray;
    }

    /**
     * @param int | string $offset
     * @param T $value
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * @param int | string $offset
     */
    public function offsetUnset($offset): void
    {
    }

    private function getNextIndex(): int
    {
        return $this->nextIndex;
    }

    private function updateNextIndex(int | string $key): void
    {
        if (is_int($key)) {
            $this->nextIndex = max($key, $this->nextIndex) + 1;
        }
    }

    /**
     * @param PersistentDictionary<T> $dictionary
     */
    private function updateFirstWhenAdding(PersistentDictionary $dictionary, int | string $key): void
    {
        if (is_null($dictionary->first)) {
            $dictionary->first = $key;
        }
    }

    /**
     * @param PersistentDictionary<T> $dictionary
     * @phpstan-impure
     */
    private function updateLastWhenAdding(PersistentDictionary $dictionary, int | string $key): void
    {
        if (!is_null($dictionary->last)) {
            $lastNodes = &$dictionary->getLeafArray($this->last);
            $newLastNodes = $lastNodes;
            $newLastNodes[$dictionary->last] = $newLastNodes[$dictionary->last]->withNext($key);
            $lastNodes = $newLastNodes;
        }

        $dictionary->last = $key;
    }

    /**
     * @param PersistentDictionary<T> $dictionary
     */
    private function setPriorNext(PersistentDictionary $dictionary, int | string $key): void
    {
        $nodes = &$dictionary->createLeafArray($key);

        $prior = $nodes[$key]->prior();
        $next = $nodes[$key]->next();

        if (!is_null($prior)) {
            $nodesPrior = &$dictionary->getLeafArray($prior);
            $nodes = $nodesPrior;
            $nodes[$prior] = $nodes[$prior]->withNext($next);
            $nodesPrior = $nodes;
        }

        if (!is_null($next)) {
            $nodesNext = &$dictionary->getLeafArray($next);
            $nodes = $nodesNext;
            $nodes[$next] = $nodes[$next]->withPrior($prior);
            $nodesNext = $nodes;
        }
    }

    /**
     * @param array<int | string, Node> $nodes
     */
    private function unsetNode(array &$nodes, int | string $key): void
    {
        $newNodes = $nodes;
        unset($newNodes[$key]);
        $nodes = $newNodes;
    }
}
