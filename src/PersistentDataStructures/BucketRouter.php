<?php

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use JetBrains\PhpStorm\Pure;
use function array_slice;
use function ord;
use function strlen;

class BucketRouter
{
    protected int $bucketsDepth;

    final public function __construct(int $bucketsDepth)
    {
        $this->bucketsDepth = $bucketsDepth;
    }

    #[Pure] public static function create(int $bucketsDepth): static
    {
        return new static($bucketsDepth);
    }

    /**
     * @return array<int>
     * @noinspection PhpAttributeCanBeAddedToOverriddenMemberInspection
     */
    #[Pure] public function getBuckets(string $index): array
    {
        return $this->fold($this->expand($index));
    }

    /**
     * @phpstan-return array<int>
     * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
     */
    #[Pure] protected function expand(string $index): array
    {
        // Expand the index char by char, by splitting low and high nibble of its ascii code
        $expanded = [];
        $length = strlen($index);
        for ($i = 0; $i < $length; $i++) {
            $character = ord($index[$i]) & 0xff;
            $expanded[] = $character & 0x0f;
            $expanded[] = $character >> 4;
        }

        // Remove first level
        array_shift($expanded);

        // Pad with 0s
        /** @infection-ignore-all */
        while (count($expanded) < $this->bucketsDepth) {
            $expanded[] = 0;
        }

        return $expanded;
    }

    /**
     * @phpstan-param array<int> $index
     * @phpstan-return array<int>
     * @noinspection PhpPureFunctionMayProduceSideEffectsInspection
     */
    #[Pure] private function fold(array $index): array
    {
        // Folds the string by XORing the characters by module of the length
        $folded = array_slice($index, 0, $this->bucketsDepth);
        $count = count($index);
        for ($i = $this->bucketsDepth; $i < $count; $i++) {
            $folded[$i % $this->bucketsDepth] ^= $index[$i];
        }

        // Reverse the array to obtain less dispersion (more memory performant for small dictionaries)
        return array_reverse($folded);
    }
}
