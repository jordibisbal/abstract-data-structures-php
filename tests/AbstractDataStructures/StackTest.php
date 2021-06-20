<?php /** @noinspection PhpArrayShapeAttributeCanBeAddedInspection */
declare(strict_types=1);

namespace AbstractDataStructures\Tests;

use AbstractDataStructures\Exceptions\UnableToRetrieveValue;
use AbstractDataStructures\Exceptions\UnableToRotateValues;
use AbstractDataStructures\Exceptions\UnableToSetValue;
use AbstractDataStructures\Exceptions\UnableToSwapValues;
use AbstractDataStructures\Tests\Stubs\TestItem;
use AbstractDataStructures\Tests\Stubs\TestStack;
use JetBrains\PhpStorm\Pure;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

final class StackTest extends testCase
{
    public function testCanBeCreatedEmpty(): void
    {
        assertTrue(TestStack::createEmpty()->isEmpty());
    }

    public function testTopOfAnEmptyCollectionThrowException(): void
    {
        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Unable to retrieve values as the structure is empty.');

        TestStack::createEmpty()->top();
    }

    public function testCanBeCreatedFromAnArray(): void
    {
        [,$item] = TestStack::fromArray($this->anArray())->pop();

        assertEquals('C', $item->value);
    }

    public function testItemsAreRetrievedInOrder(): void
    {
        /** @var TestStack $stack */
        [$stack, $item] = TestStack::fromArray($this->anArray())->pop();

        assertEquals('C', $item->value);

        [$stack, $item] = $stack->pop();

        assertEquals('B', $item->value);

        [$stack, $item] = $stack->pop();

        assertEquals('A', $item->value);
        assertTrue($stack->isEmpty());
    }

    public function testItemsAreRetrievedInVerseOrderThanArePushed(): void
    {
        $stack = TestStack::createEmpty();
        $stack = $stack->push(new TestItem('A'));
        $stack = $stack->push(new TestItem('B'));
        $stack = $stack->push(new TestItem('C'));

        [$stack, $item] = $stack->pop();
        assertEquals('C', $item->value);

        [$stack, $item] = $stack->pop();

        assertEquals('B', $item->value);

        [$stack, $item] = $stack->pop();

        assertEquals('A', $item->value);
        assertTrue($stack->isEmpty());
    }

    public function testTOSCanBeSwapped(): void
    {
        $stack = TestStack::fromArray([new TestItem('B'), new TestItem('A')]);

        $stack = $stack->swap();

        assertEquals('B', $stack->top()->value);
        [$stack, $item] = $stack->pop();
        assertEquals('B', $item->value);

        assertEquals('A', $stack->top()->value);
        [$stack, $item] = $stack->pop();
        assertEquals('A', $item->value);
        assertTrue($stack->isEmpty());
    }

    public function testSwappingAStackWithNoElementsFail(): void
    {
        $this->expectException(UnableToSwapValues::class);
        $this->expectExceptionMessage(
            'Unable to swap values as two values in the stack are required but there is only 0.'
        );
        $stack = TestStack::createEmpty();
        $stack->swap();
    }

    public function testSwappingAStackWithOneElementsFail(): void
    {
        $this->expectException(UnableToSwapValues::class);
        $this->expectExceptionMessage(
            'Unable to swap values as two values in the stack are required but there is only 1.'
        );
        $stack = TestStack::fromArray([new TestItem('A')]);
        $stack->swap();
    }

    public function testTOSCanBeRotated(): void
    {
        $stack = TestStack::fromArray([new TestItem('C'), new TestItem('B'), new TestItem('A')]);

        $stack = $stack->rotate();

        assertEquals('C', $stack->top()->value);
        [$stack, $item] = $stack->pop();
        assertEquals('C', $item->value);

        assertEquals('A', $stack->top()->value);
        [$stack, $item] = $stack->pop();
        assertEquals('A', $item->value);

        assertEquals('B', $stack->top()->value);
        [$stack, $item] = $stack->pop();
        assertEquals('B', $item->value);
        assertTrue($stack->isEmpty());
    }

    public function testRotatingAStackWithNoElementsFail(): void
    {
        $this->expectException(UnableToRotateValues::class);
        $this->expectExceptionMessage(
            'Unable to rotate values as three values in the stack are required but there are only 0.'
        );
        $stack = TestStack::createEmpty();
        $stack->rotate();
    }

    public function testRotationAStackWithOneElementsFail(): void
    {
        $this->expectException(UnableToRotateValues::class);
        $this->expectExceptionMessage(
            'Unable to rotate values as three values in the stack are required but there are only 1.'
        );
        $stack = TestStack::fromArray([new TestItem('A')]);
        $stack->rotate();
    }
    
    public function testRotationAStackWithTwoElementsFail(): void
    {
        $this->expectException(UnableToRotateValues::class);
        $this->expectExceptionMessage(
            'Unable to rotate values as three values in the stack are required but there are only 2.'
        );
        $stack = TestStack::fromArray([new TestItem('A'), new TestItem('B')]);
        $stack->rotate();
    }
    
    public function testWhenPopTheLengthDecreases(): void
    {
        $stack = TestStack::fromArray($this->anArray());

        $originalSize = $stack->length();

        [$stack, ] = $stack->pop();

        assertEquals($originalSize - 1, $stack->length());
    }

    public function testWhenPopAnEmptyQueueAnExceptionIsThrown(): void
    {
        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Unable to retrieve values as the structure is empty');

        TestStack::createEmpty()->pop();
    }

    public function testLengthIsCount(): void
    {
        $stack = TestStack::fromArray($this->anArray());
        assertEquals($stack->count(), $stack->length());
    }

    public function testAPositionCanBePeeked(): void
    {
        $stack = TestStack::fromArray($this->anArray());

        assertEquals('A', $stack->peek(1)->value);
        assertEquals('B', $stack->peek(2)->value);
        assertEquals('C', $stack->peek(3)->value);
        assertEquals('A', $stack->peek(-3)->value);
        assertEquals('B', $stack->peek(-2)->value);
        assertEquals('C', $stack->peek(-1)->value);
    }
    
    public function testFailsWhenPositionZeroIsPeeked(): void
    {
        $stack = TestStack::fromArray($this->anArray());

        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('Zero position is invalid');

        $stack->peek(0);
    }

    public function testFailsWhenPositionIfBeyondEndOfTheQueue(): void
    {
        $stack = TestStack::fromArray($this->anArray());

        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('No such position exists, asked for 4 but only 3 available.');

        $stack->peek(4);
    }

    public function testFailsWhenPositionIfBelowStartOfTheQueue(): void
    {
        $stack = TestStack::fromArray($this->anArray());

        $this->expectException(UnableToRetrieveValue::class);
        $this->expectExceptionMessage('No such position exists, asked for -4 but only 3 available.');

        $stack->peek(-4);
    }

    public function testFailsWhenCreatingFromAnArrayWithInvalidTypes(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type string but '
            . 'AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        TestStack::fromArray($this->aWrongTypedArray());
    }

    public function testFailsWhenQueuesWithInvalidType(): void
    {
        $this->expectException(UnableToSetValue::class);
        $this->expectExceptionMessage(
            'Unable to set value as the given item is of type string but '
            . 'AbstractDataStructures\Tests\Stubs\TestItem expected.'
        );

        TestStack::createEmpty()->push('wrong');
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
