<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
declare(strict_types=1);

namespace j45l\AbstractDataStructures\Tests;

use j45l\AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use j45l\AbstractDataStructures\Exceptions\UnableToSetValue;
use j45l\AbstractDataStructures\Tests\Stubs\TestItem;
use j45l\AbstractDataStructures\Tests\Stubs\TestQueue;
use j45l\either\None;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

final class QueueTest extends testCase
{
    public function testCanBeCreatedEmpty(): void
    {
        assertTrue(TestQueue::createEmpty()->isEmpty());
    }

    public function testHeadOfAnEmptyCollectionThrowException(): void
    {
        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Unable to retrieve values as the structure is empty.');

        TestQueue::createEmpty()->head();
    }

    public function testTailOfAnEmptyCollectionThrowException(): void
    {
        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Unable to retrieve values as the structure is empty.');

        TestQueue::createEmpty()->tail();
    }

    public function testCanBeCreatedFromAnArray(): void
    {
        [,$item] = TestQueue::fromArray($this->anArray())->dequeue();

        assertEquals('C', $item->value);
    }

    public function testItemsAreRetrievedInOrder(): void
    {
        /** @var TestQueue $queue */
        [$queue, $item] = TestQueue::fromArray($this->anArray())->dequeue();

        assertEquals('C', $item->value);

        [$queue, $item] = $queue->dequeue();

        assertEquals('B', $item->value);

        [$queue, $item] = $queue->dequeue();

        assertEquals('A', $item->value);
        assertTrue($queue->isEmpty());
    }

    public function testItemsAreRetrievedAsQueued(): void
    {
        $queue = TestQueue::createEmpty();
        $queue = $queue->queue(new TestItem('A'));
        $queue = $queue->queue(new TestItem('B'));
        $queue = $queue->queue(new TestItem('C'));

        [$queue, $item] = $queue->dequeue();
        assertEquals('A', $item->value);

        [$queue, $item] = $queue->dequeue();

        assertEquals('B', $item->value);

        [$queue, $item] = $queue->dequeue();

        assertEquals('C', $item->value);
        assertTrue($queue->isEmpty());
    }

    public function testWhenDequeuedTheLengthDecreases(): void
    {
        $queue = TestQueue::fromArray($this->anArray());

        $originalSize = $queue->length();

        [$queue, ] = $queue->dequeue();

        assertEquals($originalSize - 1, $queue->length());
    }

    public function testWhenDequeueAnEmptyQueueAnExceptionIsThrown(): void
    {
        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Unable to retrieve values as the structure is empty');

        TestQueue::createEmpty()->dequeue();
    }

    public function testLengthIsCount(): void
    {
        $queue = TestQueue::fromArray($this->anArray());
        assertEquals($queue->count(), $queue->length());
    }

    public function testAPositionCanBePeeked(): void
    {
        $queue = TestQueue::fromArray($this->anArray());

        assertEquals('A', $queue->peek(1)->value);
        assertEquals('B', $queue->peek(2)->value);
        assertEquals('C', $queue->peek(3)->value);
        assertEquals('A', $queue->peek(-3)->value);
        assertEquals('B', $queue->peek(-2)->value);
        assertEquals('C', $queue->peek(-1)->value);
    }

    public function testFailsWhenPositionZeroIsPeeked(): void
    {
        $queue = TestQueue::fromArray($this->anArray());

        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Zero position is invalid');

        $queue->peek(0);
    }

    public function testFailsWhenPositionIfBeyondEndOfTheQueue(): void
    {
        $queue = TestQueue::fromArray($this->anArray());

        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('No such position exists, asked for 4 but only 3 available.');

        $queue->peek(4);
    }

    public function testFailsWhenPositionIfBelowStartOfTheQueue(): void
    {
        $queue = TestQueue::fromArray($this->anArray());

        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('No such position exists, asked for -4 but only 3 available.');

        $queue->peek(-4);
    }

    public function testFailsWhenCreatingFromAnArrayWithInvalidTypes(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type string but '
            . 'j45l\AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        TestQueue::fromArray($this->aWrongTypedArray());
    }

    public function testFailsWhenQueuesWithInvalidType(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type string but '
            . 'j45l\AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        TestQueue::createEmpty()->queue('wrong');
    }

    #[Pure] private function anArray(): array
    {
        return [
            'a' => new TestItem('A'),
            'b' => new TestItem('B'),
            'c' => new TestItem('C')
         ];
    }

    #[Pure] private function aWrongTypedArray(): array
    {
        return [
            'a' => new TestItem('A'),
            'b' => new TestItem('B'),
            'c' => 'wrong'
        ];
    }
}
