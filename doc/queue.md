# Queue

A queue structure, items can be queued (put in the queue), dequeued (retrieved) from the queue, the items are retrieved in the same order that are put on the queue.

* [count](#public-function-count-int)
* [createEmpty](#static-function-createempty-static)  
* [dequeue](#public-function-dequeue-array)
* [fromArray](#public-static-function-fromarrayarray-items-static)  
* [head](#public-function-head-mixed)
* [isEmpty](#public-function-isempty-bool)  
* [length](#public-function-length-int)
* [queue](#public-function-queuemixed-item-static)
* [tail](#public-function-tail-mixed)
* [type](#abstract-public-function-type-string)

## Methods

### public function count(): int

Returns the number of items in the collection

### static function createEmpty(): static

Return a new empty collection

### public function dequeue(): array

Returns the element on the head of the queue and a new queue without the element.

```
$queue = MixedQueue::fromArray([Mixed::from(123)]);

[$newQueue, $element] = $queue->dequeue();

$element            // (Mixed) 123
$queue->Length()    // 1
$newQueue->Length() // 0
```

### public static function fromArray(array $items): static

Returns a new queue initialized with ```$items``` items

### public function head(): mixed

Returns (peeks) the next element to be dequeue.

```
$queue = MixedQueue::fromArray([Mixed::from(123), Mixed::from(456)]);

$element = $queue->tail;

$element            // (Mixed) 456
$queue->Length()    // 2
```

### public function isEmpty(): bool

Checks whenever the collection is empty


### public function length(): int

Alias to [count()](#public-function-count-int).

### public function queue(mixed $item): static

Adds the element to the queue, returns a new queue with the element in within.

```
$queue = MixedQueue::createEmpty();

$newQueue = $queue->queue(new Mixed(123));

$queue->Length()    // 0
$newQueue->Length() // 1
```

### public function tail(): mixed

Returns (peeks) the last enqueued element.

```
$queue = MixedQueue::fromArray([Mixed::from(123), Mixed::from(456)]);

$element = $queue->tail;

$element            // (Mixed) 123
$queue->Length()    // 2
```

### abstract public function type(): string

Should return the queue type class name