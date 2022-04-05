<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use Countable;
use j45l\AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable] abstract class Queue extends TypedArrayBasedStructure implements Countable
{

    public function queue(mixed $item): static
    {
        $this->guardSet($item);

        $itemsArray = $this->itemsArray->unshift($item);

        return new static($itemsArray);
    }

    public function dequeue(): array
    {
        [$itemsArray, $item] = $this->itemsArray->pop();

        return [new static($itemsArray), $item];
    }

    public function tail(): mixed
    {
        return $this->itemsArray->first();
    }

    /**
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
}
