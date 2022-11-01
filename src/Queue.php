<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use Countable;
use Generator;
use j45l\AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

/**
 * @template T
 * @extends TypedArrayBasedStructure<T>
 */
#[Immutable] abstract class Queue extends TypedArrayBasedStructure implements Countable
{
    /** @param T $item */
    public function queue(mixed $item): static
    {
        $this->guardSet($item);

        $itemsArray = $this->itemsArray->unshift($item);

        return new static($itemsArray);
    }

    /** @return array{Queue<T>, T}  */
    public function dequeue(): array
    {
        [$itemsArray, $item] = $this->itemsArray->pop();

        return [new static($itemsArray), $item];
    }

    /**
     * @return T
     * @throws UnableToRetrieveValue
     */
    public function tail(): mixed
    {
        return $this->itemsArray->first();
    }

    /**
     * @return T
     * @throws UnableToRetrieveValue
     */
    public function head(): mixed
    {
        return $this->itemsArray->last();
    }

    #[Pure] public function length(): int
    {
        return $this->count();
    }

    public function yield(): Generator
    {
        foreach (array_reverse($this->itemsArray->asArray(), true) as $key => $value) {
            yield $key => $value;
        }
    }
}
