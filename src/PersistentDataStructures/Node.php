<?php

namespace j45l\AbstractDataStructures\PersistentDataStructures;

use JetBrains\PhpStorm\Pure;

class Node
{
    private string|int|null $prior;
    private string|int|null $next;
    private mixed $value;

    public function __construct(null | string | int $prior, null | string | int $next, mixed $value)
    {
        $this->prior = $prior;
        $this->next = $next;
        $this->value = $value;
    }

    public function prior(): null|int|string
    {
        return $this->prior;
    }

    public function next(): null|int|string
    {
        return $this->next;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    #[Pure] public function withNext(int | string | null $key): Node
    {
        return new self($this->prior, $key, $this->value);
    }

    #[Pure] public function withPrior(int | string | null $key): Node
    {
        return new self($key, $this->next, $this->value);
    }
}
