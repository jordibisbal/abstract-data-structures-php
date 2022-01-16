<?php

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use Closure;
use function Functional\memoize;

class MemoizedBucketRouter extends BucketRouter
{
    /**
     * @return array<int>
     */
    public function getBuckets(string $index): array
    {
        return memoize(fn ($index) => parent::getBuckets($index), [$index], [self::class, $index]);
    }
}
