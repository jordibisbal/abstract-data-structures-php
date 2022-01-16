<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures;

use Countable;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable] abstract class Stack extends TypedArrayBasedStructure implements Countable
{

    public function push(mixed $item): static
    {
        $this->guardSet($item);

        $itemsArray = $this->itemsArray->push($item);

        return new static($itemsArray);
    }

    public function pop(): array
    {
        [$itemsArray, $item] = $this->itemsArray->pop();

        return [new static($itemsArray), $item];
    }

    #[Pure] public function top(): mixed
    {
        return $this->itemsArray->last();
    }

    #[Pure] public function length(): int
    {
        return $this->count();
    }
    
    public function swap(): static
    {
        $itemsArray = $this->itemsArray->swap();

        return new static($itemsArray);
    }

    public function rotate(): static
    {
        $itemsArray = $this->itemsArray->rotate();

        return new static($itemsArray);
    }
}
