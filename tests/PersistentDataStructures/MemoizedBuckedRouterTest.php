<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\PersistentDataStructures;

use j45l\AbstractDataStructures\PersistentDataStructures\MemoizedBucketRouter;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type MemoizedCallCountableBucketRouter MemoizedBucketRouter & CallCounting
 **/
final class MemoizedBuckedRouterTest extends TestCase
{
    /** @noinspection PhpExpressionAlwaysNullInspection */
    public function setUp(): void
    {
        parent::setUp();

        (static function (): void {
            /** @phpstan-ignore-next-line */
            self::memoizeTraitForget();
        })->bindTo(null, MemoizedBucketRouter::class)();
    }

    public function testTwoMemoizedBucketRoutersDoesNotCollisionCreatingKeys(): void
    {
        [$routerA, $routerB] = $this->getTwoBuckets();
        self::assertEquals([15], $routerA->getBuckets('AA'));
        self::assertEquals([9, 6], $routerB->getBuckets('AA'));
    }

    public function testForAGivenKeyAMemoizedBucketCalculatesJustOnce(): void
    {
        [$routerA] = $this->getTwoBuckets();

        $routerA->getBuckets('AA');
        self::assertEquals(1, $routerA->calls());

        $routerA->getBuckets('AA');
        self::assertEquals(1, $routerA->calls());
    }

    public function testForDifferentKeysAMemoizedBucketCalculatesForEveryOne(): void
    {
        [$routerA] = $this->getTwoBuckets();

        $routerA->getBuckets('AA');
        $routerA->getBuckets('AB');
        $routerA->getBuckets('ABA');
        self::assertEquals(3, $routerA->calls());
    }

    /**
     * @return MemoizedCallCountableBucketRouter[]
     */
    private function getTwoBuckets(): array
    {
        return [
            new class(1) extends MemoizedBucketRouter implements CallCounting {
                public int $calls = 0;
                public function expand(string $index): array
                {
                    $this->calls++;
                    return parent::expand($index);
                }

                public function calls(): int
                {
                    return $this->calls;
                }
            },
            new class(2) extends MemoizedBucketRouter implements CallCounting {
                public int $calls = 0;
                public function expand(string $index): array
                {
                    $this->calls++;
                    return parent::expand($index);
                }

                public function calls(): int
                {
                    return $this->calls;
                }
            }
        ];
    }
}
