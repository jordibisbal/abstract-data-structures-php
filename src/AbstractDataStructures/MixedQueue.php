<?php
declare(strict_types=1);

namespace AbstractDataStructures;


final class MixedQueue extends Queue
{

    public function type(): string
    {
        return Mixed::class;
    }
}