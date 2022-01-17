<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use j45l\AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use j45l\AbstractDataStructures\Exceptions\UnableToSetValue;
use j45l\AbstractDataStructures\PersistentDataStructures\PersistentArray;
use Closure;
use JetBrains\PhpStorm\Pure;
use function Functional\each;

/** @template T */
abstract class TypedArrayBasedStructure
{
    /** @var PersistentArray<T>  */
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

    /**
     * @param Array<T> $items
     * @throws UnableToSetValue
     */
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

    /**
     * @param Array<T> $items
     * @throws UnableToSetValue
     */
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

    /**
     * @throws UnableToSetValue
     */
    private function assertIsACorrectTypeOrFail(): Closure
    {
        return function ($item) {
            if (is_a($item, $this->type())) {
                return;
            }

            throw UnableToSetValue::becauseTheItemIsNotOfTheProperType($item, $this->type());
        };
    }
}
