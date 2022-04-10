<?php
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests\Stubs;

use j45l\AbstractDataStructures\UniqueIndexed;

final class UniqueIndexedTestItem extends TestItem implements UniqueIndexed
{
    public function getUniqueKey(): string
    {
        return $this->value;
    }
}
