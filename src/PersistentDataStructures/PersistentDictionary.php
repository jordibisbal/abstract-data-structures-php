<?php

declare(strict_types=1);

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use ArrayAccess;
use Countable;
use j45l\either\Failure;
use j45l\either\None;
use j45l\either\Reason;
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
    private array $bucket;

    private int $nextIndex;

    private int $count;

    /** @var key */
    private int | string | null $first;

    /** @var key  */
    private int | string | null $last;

    private BucketRouter $bucketRouter;

    /**
     * @param array<T> $items
     */
    private function __construct(array $items, BucketRouter $bucketRouter)
    {
        $this->bucketRouter = $bucketRouter;
        $this->bucket = [];
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
        return new self($items, new BucketRouter(self::SHARD_DEPTH));
    }

    /**
     * @return PersistentDictionary<T>
     */
    public function withBucketRouter(BucketRouter $bucketRouter): PersistentDictionary
    {
        return new self($this->asArray(), $bucketRouter);
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
        return array_key_exists($offset, $this->getLeafBucket($offset));
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

        $bucket = &$new->createLeafBucket($key);

        $new->setPreviousNext($new, $key);
        $new->unsetNode($bucket, $key);

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

        return new self($newArray, $this->bucketRouter);
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
     * @phpstan-impure
     * @phpstan-return array<key, mixed>
     */
    private function &createLeafBucket(mixed $index): array
    {
        $bucket = &$this->bucket;

        foreach ($this->bucketRouter->getBuckets((string) $index) as $branch) {
            if (!array_key_exists($branch, $bucket)) {
                $bucket[$branch] = [];
            }
            $bucket = &$bucket[$branch];
        }

        return $bucket;
    }

    /**
     * @return array<Node>
     */
    private function &getLeafBucket(mixed $index): array
    {
        $bucket = &$this->bucket;

        foreach ($this->bucketRouter->getBuckets((string) $index) as $branch) {
            if (!array_key_exists($branch, $bucket)) {
                $array = [];
                return $array;
            }
            $bucket = &$bucket[$branch];
        }

        return $bucket;
    }

    /**
     * @param int | string  $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @var array<T> $bucket */
        $bucket = &$this->bucket;

        foreach ($this->bucketRouter->getBuckets((string) $offset) as $branch) {
            if (!array_key_exists($branch, $bucket)) {
                return false;
            }
            $bucket = &$bucket[$branch];
        }

        return array_key_exists($offset, $bucket);
    }

    /**
     * @param int | string $offset
     * @phpstan-return Node
     */
    private function getNode(int | string $offset): mixed
    {
        return $this->getLeafBucket($offset)[$offset];
    }

    /**
     * @param int | string $offset
     * @phpstan-return T | Failure
     */
    public function offsetGet($offset): mixed
    {
        if (!array_key_exists($offset, $this->getLeafBucket($offset))) {
            return Failure::from(
                Reason::from(sprintf('Element with index [%s] does not exist.', (string) $offset))
            );
        }

        return $this->getLeafBucket($offset)[$offset]->value();
    }

    private function setNode(mixed $key, mixed $value): void
    {
        $bucket = &$this->createLeafBucket($key);

        $this->updateNextIndex($key);

        if (array_key_exists($key, $bucket) && ($bucket[$key] === $value)) {
            return;
        }

        $newBucket = $bucket;
        $node = new Node(null, null, $value);

        if (!array_key_exists($key, $newBucket)) {
            $this->count++;
            $node = new Node($this->last, null, $value);

            $this->updateFirstWhenAdding($this, $key);
            $this->updateLastWhenAdding($this, $key);
        }

        $newBucket[$key] = $node;
        $bucket = $newBucket;
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
            $lastBucket = &$dictionary->getLeafBucket($this->last);
            $newLastBucket = $lastBucket;
            $newLastBucket[$dictionary->last] = $newLastBucket[$dictionary->last]->withNext($key);
            $lastBucket = $newLastBucket;
        }

        $dictionary->last = $key;
    }

    /**
     * @param PersistentDictionary<T> $dictionary
     */
    private function setPreviousNext(PersistentDictionary $dictionary, int | string $key): void
    {
        $bucket = &$dictionary->createLeafBucket($key);

        $prior = $bucket[$key]->previous();
        $next = $bucket[$key]->next();

        if (!is_null($prior)) {
            $priorBucket = &$dictionary->getLeafBucket($prior);
            $bucket = $priorBucket;
            $bucket[$prior] = $bucket[$prior]->withNext($next);
            $priorBucket = $bucket;
        }

        if (!is_null($next)) {
            $nextBucket = &$dictionary->getLeafBucket($next);
            $bucket = $nextBucket;
            $bucket[$next] = $bucket[$next]->withPrevious($prior);
            $nextBucket = $bucket;
        }
    }

    /**
     * @param array<int | string, Node> $bucket
     */
    private function unsetNode(array &$bucket, int | string $key): void
    {
        $newNodes = $bucket;
        unset($newNodes[$key]);
        $bucket = $newNodes;
    }
}
