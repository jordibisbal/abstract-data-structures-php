<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\PersistentDataStructures;

use j45l\AbstractDataStructures\PersistentDataStructures\BucketRouter;
use PHPUnit\Framework\TestCase;

final class BucketRouterTest extends TestCase
{
    /**
     * @return mixed[]
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function bucketExpandCases(): array
    {
        return [
            'A'    => ['A',    [0, 4]], // 14       => -MSNib => 40      => 40                => 40 => 04
            'b'    => ['b',    [0, 6]], // 26       => -MSNib => 60      => 60                => 60 => 06
            'AB'   => ['AB',   [2, 0]], // 1424     => -MSNib => 4240    => 42 ^ 40           => 02 => 20
            'ab'   => ['ab',   [2, 0]], // 1626     => -MSNib => 6260    => 62 ^ 60           => 02 => 20
            '1a'   => ['1b',   [2, 5]], // 1326     => -MSNib => 3260    => 32 ^ 60           => 52 => 25
            '1a0B' => ['1a0B', [3, 2]], // 13160324 => -MSNib => 3160324 => 31 ^ 60 ^ 32 ^ 40 => 23 => 32
        ];
    }

    /**
     * @dataProvider bucketExpandCases
     * @param int[] $keys
     */
    public function testBuildTheBucketAsExpected(string $key, array $keys): void
    {
        $router = BucketRouter::create(2);
        $buckets = $router->getBuckets($key);
        self::assertEquals($keys, $buckets);
    }
}
