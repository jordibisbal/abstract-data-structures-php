<?php

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use j45l\functional\Optimization\MemoizeTrait;

class MemoizedBucketRouter extends BucketRouter
{
    /** @use MemoizeTrait<array> */
    use MemoizeTrait;

    /**
     * @return array<int>
     * @phpstan-impure
     */
    public function getBuckets(string $index): array
    {
        /** @phpstan-ignore-next-line */
        return self::memoize(
            fn ($index): array => parent::getBuckets($index),
        )(
            self::class,
            $this->bucketsDepth,
            $index
        );
    }
}
