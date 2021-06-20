<?php
declare(strict_types=1);

namespace AbstractDataStructures;

use AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use AbstractDataStructures\Exceptions\UnableToSetValue;
use AbstractDataStructures\PersistentDataStructures\PersistentArray;
use Closure;
use JetBrains\PhpStorm\Pure;
use function Functional\each;
use function JBFunctional\assertIsAOr;

abstract class TypedArrayBasedStructure
{
    protected PersistentArray $itemsArray;

    abstract public function type(): string;

    #[Pure] final protected function __construct(PersistentArray $items)
    {
        $this->itemsArray = $items;
    }

    public static function createEmpty(): static
    {
        return static::fromArray([]);
    }

    #[Pure] public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /** @throws UnableToSetValue */
    public static function fromArray(array $items): static
    {
        $dataStructure = new static(PersistentArray::fromArray([]));
        $dataStructure->guardArraySet($items);
        $dataStructure->itemsArray = PersistentArray::fromArray($items);

        return $dataStructure;
    }

    #[Pure] public function count(): int
    {
        return $this->itemsArray->count();
    }

    /** @throws UnableToSetValue */
    protected function guardSet(mixed $item): void
    {
        if (!is_a($item, $this->type())) {
            throw UnableToSetValue::becauseTheItemIsNotOfTheProperType($item, $this->type());
        }
    }

    /** @throws UnableToSetValue */
    private function guardArraySet(array $items): void
    {
        each(
            $items,
            fn ($item) => $this->assertIsACorrectTypeOrFail()($item)
        );
    }

    /** @throws UnableToRetrieveValue */
    public function peek(int $position): mixed
    {
        return $this->itemsArray->peek($position);
    }

    private function assertIsACorrectTypeOrFail(): Closure
    {
        return function ($item) {
            assertIsAOr(
                $this->type(),
                function ($item, $type) {
                    throw UnableToSetValue::becauseTheItemIsNotOfTheProperType($item, $type);
                }
            )($item);
        };
    }
}
