<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\PersistentDataStructures;

interface CallCounting
{
    public function calls(): int;
}