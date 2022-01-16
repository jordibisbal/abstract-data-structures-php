<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use j45l\AbstractDataStructures\Exceptions\UnableToSetValue;
use j45l\AbstractDataStructures\PersistentDataStructures\PersistentDictionary;
use Closure;
use JetBrains\PhpStorm\Pure;
use function Functional\each;

/** @template T */
abstract class TypedDictionaryBasedStructure
{
    /** @var PersistentDictionary<T>  */
    protected PersistentDictionary $itemsArray;

    abstract public function type(): string;

    /**
     * @param PersistentDictionary<T> $items
     */
    final protected function __construct(PersistentDictionary $items)
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
     * @param array<T> $items
     * @throws UnableToSetValue
     */
    public static function fromArray(array $items): static
    {
        $dataStructure = new static(PersistentDictionary::fromArray([]));
        $dataStructure->guardArraySet($items);
        $dataStructure->itemsArray = PersistentDictionary::fromArray($items);

        return $dataStructure;
    }

    #[Pure] public function count(): int
    {
        return $this->itemsArray->count();
    }

    /**
     * @param T $item
     * @throws UnableToSetValue
     */
    protected function guardSet(mixed $item): void
    {
        if (!is_a($item, $this->type())) {
            throw UnableToSetValue::becauseTheItemIsNotOfTheProperType($item, $this->type());
        }
    }

    /**
     * @param array<T> $items
     * @throws UnableToSetValue
     */
    private function guardArraySet(array $items): void
    {
        each(
            $items,
            fn ($item) => $this->assertIsACorrectTypeOrFail()($item)
        );
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
