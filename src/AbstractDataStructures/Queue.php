<?php
declare(strict_types=1);

namespace AbstractDataStructures;

use Countable;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use phpDocumentor\Reflection\Types\Integer;

#[Immutable] abstract class Queue implements Countable
{
    use TypedArrayBasedTrait;

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

    #[Pure] public function tail(): mixed
    {
        return $this->itemsArray->first();
    }

    #[Pure] public function head(): mixed
    {
        return $this->itemsArray->last();
    }

    #[Pure] public function length(): int
    {
        return $this->count();
    }
}