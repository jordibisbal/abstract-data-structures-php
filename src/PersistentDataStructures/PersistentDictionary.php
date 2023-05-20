<?php

declare(strict_types=1);

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use ArrayAccess;
use Countable;
use Generator;
use j45l\Cats\Maybe\Maybe;
use JetBrains\PhpStorm\Pure;

use function array_key_exists;
use function is_int;
use function is_null;
use function j45l\Cats\Maybe\None;
use function j45l\Cats\Maybe\Some;
use function j45l\functional\map;

/**
 * @template T
 * @implements ArrayAccess<int | string, T>
 * @phpstan-type key int | string | null
 */
final class PersistentDictionary implements Countable, ArrayAccess
{
    private const BUCKET_DEPTH = 4;

    /** @var array<key, mixed> */
    private array $bucket;

    private int $nextIntKey;

    private int $count;

    /** @phpstan-var key */
    private int | string | null $first;

    /** @phpstan-var key  */
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
        $this->nextIntKey = 0;

        map($items, fn ($value, $key) => $this->setNode($key, $value));
    }

    /**
     * @param array<T> $items
     * @return PersistentDictionary<T>
     * @phpstan-pure
     */
    public static function fromArray(array $items, BucketRouter $bucketRouter = null): PersistentDictionary
    {
        return new self($items, $bucketRouter ?? BucketRouter::create(self::BUCKET_DEPTH));
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
        $dictionary = clone $this;
        $dictionary->setNode($key, $value);

        return $dictionary;
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
        $newArray->setNode($newArray->getNextIntKey(), $value);

        return $newArray;
    }

    /**
     * @return Maybe<T>
     */
    #[Pure]
    public function last(): Maybe
    {
        return match (true) {
            !$this->last => None(),
            default => $this->offsetGet($this->last)
        };
    }

    /**
     * @return Maybe<T>
     */
    #[Pure]
    public function first(): Maybe
    {
        return match (true) {
            !$this->first => None(),
            default => $this->offsetGet($this->first)
        };
    }

    #[Pure] public function hasKey(int | string $offset): bool
    {
        return array_key_exists($offset, $this->getLeafBucket($offset));
    }

    /**
     * @return PersistentDictionary<T>
     * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
     */
    #[Pure] public function unset(int | string $key): PersistentDictionary
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

    #[Pure] public function each(callable $fn): void
    {
        $key = $this->first;
        while ($key !== null) {
            $node = $this->getNode($key);
            $fn($node->value(), $key);
            $key = $node->next();
        }
    }

    /** @return Generator<T> */
    #[Pure] public function yield(): Generator
    {
        $key = $this->first;
        while ($key !== null) {
            $node = $this->getNode($key);
            yield $key => $node->value();
            $key = $node->next();
        }
    }

    /**
     * @return PersistentDictionary<T>
     * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
     */
    #[Pure] public function sort(callable $sort): PersistentDictionary
    {
        $newArray = $this->asArray();
        uasort($newArray, $sort);

        return new self($newArray, $this->bucketRouter);
    }

    /** @return array<T> */
    #[Pure] public function asArray(): array
    {
        $collected = [];
        /** @noinspection PhpExpressionResultUnusedInspection */
        $this->each(function ($item, $key) use (&$collected) {
            $collected[$key] = $item;
        });

        return $collected;
    }

    /**
     * @phpstan-impure
     * @phpstan-return array<key, mixed>
     * @noinspection PhpPureAttributeCanBeAddedInspection
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
    #[Pure] private function &getLeafBucket(mixed $index): array
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
    #[Pure] public function offsetExists(mixed $offset): bool
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
    #[Pure] private function getNode(int | string $offset): mixed
    {
        return $this->getLeafBucket($offset)[$offset];
    }

    /**
     * @param int | string $offset
     * @phpstan-return Maybe<T>
     */
    #[Pure]
    public function offsetGet($offset): Maybe
    {
        return match (true) {
            array_key_exists($offset, $this->getLeafBucket($offset)) =>
                Some($this->getLeafBucket($offset)[$offset]->value()),
            default =>
                None()
        };
    }

    private function setNode(mixed $key, mixed $value): void
    {
        /** @var array<int | string, Node> $bucket */
        $bucket = &$this->createLeafBucket($key);
        if (array_key_exists($key, $bucket) && ($bucket[$key] === $value)) {
            return;
        }

        $this->updateNextIntKey($key);
        $this->setNodeValue($bucket, $this->getOrAddNode($this, $bucket, $key), $value, $key);
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

    private function getNextIntKey(): int
    {
        return $this->nextIntKey;
    }

    private function updateNextIntKey(int | string $key): void
    {
        if (is_int($key)) {
            $this->nextIntKey = max($key, $this->nextIntKey) + 1;
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

    /**
     * @param PersistentDictionary<T> $dictionary
     * @param array<key, Node> $bucket
     */
    private function getOrAddNode(PersistentDictionary $dictionary, array $bucket, mixed $key): Node
    {
        if (array_key_exists($key, $bucket)) {
            return $bucket[$key];
        }

        $this->count++;
        $node = new Node($this->last, null, null);

        $dictionary->updateFirstWhenAdding($this, $key);
        $dictionary->updateLastWhenAdding($this, $key);

        return $node;
    }

    /**
     * @param array<key, Node> $bucket
     */
    private function setNodeValue(array &$bucket, Node $node, mixed $value, mixed $key): void
    {
        $newBucket = $bucket;
        $newBucket[$key] = $node->withValue($value);
        $bucket = $newBucket;
    }
}
