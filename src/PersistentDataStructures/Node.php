<?php

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use JetBrains\PhpStorm\Pure;

class Node
{
    private string|int|null $previous;
    private string|int|null $next;
    private mixed $value;

    public function __construct(null | string | int $prior, null | string | int $next, mixed $value)
    {
        $this->previous = $prior;
        $this->next = $next;
        $this->value = $value;
    }

    public function previous(): null|int|string
    {
        return $this->previous;
    }

    public function next(): null|int|string
    {
        return $this->next;
    }

    #[Pure]
    public function value(): mixed
    {
        return $this->value;
    }

    #[Pure] public function withNext(int | string | null $key): Node
    {
        return new self($this->previous, $key, $this->value);
    }

    #[Pure] public function withPrevious(int | string | null $key): Node
    {
        return new self($key, $this->next, $this->value);
    }

    #[Pure] public function withValue(mixed $value): Node
    {
        return new self($this->previous, $this->next, $value);
    }
}
