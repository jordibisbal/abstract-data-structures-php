<?php
declare(strict_types=1);

namespace AbstractDataStructures;


final class MixedCollection extends Collection
{

    public function type(): string
    {
        return Mixed::class;
    }
}