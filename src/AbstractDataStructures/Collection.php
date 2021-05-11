<?php
declare(strict_types=1);

namespace AbstractDataStructures;

use Countable;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable] abstract class Collection implements Countable
{
     use TypedArrayBasedTrait;

     #[Pure] public function hasKey(string $key): bool
     {
         return $this->itemsArray->hasKey($key);
     }

     #[Pure] public function get(string $key): mixed
     {
         return $this->itemsArray->get($key);
     }

     public function set(string $key, mixed $value): static
     {
         $this->guardSet($value);
         return new static($this->itemsArray->set($key, $value));
     }

     public function remove(string $key): static
     {
         return new static($this->itemsArray->unset($key));
     }

    public function append(mixed $value): static
    {
        $this->guardSet($value);
        return new static($this->itemsArray->append($value));
    }

    #[Pure] public function last(): mixed
    {
        return $this->itemsArray->last();
    }

    #[Pure] public function first(): mixed
    {
        return $this->itemsArray->first();
    }

    public function foreach(callable $fn): void
    {
        $this->itemsArray->each($fn);
    }

    public function values(): array
    {
        $result = [];
        $this->itemsArray->each(function ($value) use (&$result) : void { $result[] = $value; });

        return $result;
    }

    public function sort(callable $sortFn): static
    {
        return new static($this->itemsArray->sort($sortFn));
    }

    public function keys(): array
    {
        $result = [];
        $this->itemsArray->each(function ($value, $key) use (&$result) : void { $result[] = $key; });

        return $result;
    }

    #[Pure] public function asArray(): array
    {
        return $this->itemsArray->asArray();
    }

    #[Pure] public function size(): int
    {
        return $this->count();
    }
 }