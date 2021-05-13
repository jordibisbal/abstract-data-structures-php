# abstractDataStructures

## Immutable data structures

Persistent data structured, not optimized for big collections/arrays

All collection are typed, extend from the base type, set the allowed type by overriding ```abstract public function type(): string;```, return must the desired type classname.

When a getter operation would produce a mutation, a two elements array is returned, the first element would be the 
modified collection, and the second one the got element.

```
$queue = Queue::fromArray([1, 2]);
```

## Classes

* [Queue](#queue)
* Collection


* MixedQueue
* MixedCollection


* Mixed

### Common in all collection structures 

Collection, Queue

#### TypedArrayBasedTrait

##### abstract public function type(): string

Returns the collection type class name

##### static function createEmpty(): static
    
Return a new empty collection

###### public function isEmpty(): bool

Checks whenever the collection is empty

###### public static function fromArray(array $items): static

Returns a new collection initialized with ```$items``` items

###### public function count(): int

Returns the number of items in the collection

### Queue

A queue structure, items can be queued (put in the queue), dequeued (retrieved) from the queue, the items are retrieved in the same order that are put on the queue.

#### public function queue(mixed $item): static

Adds the element to the queue, returns a new queue with the element in within.

```
$queue = MixedQueue::createEmpty();

$newQueue = $queue->queue(new Mixed(123));

$queue->Length()    // 0
$newQueue->Length() // 1
```

#### public function dequeue(): array

Returns the element on the head of the queue and a new queue without the element.

```
$queue = MixedQueue::fromArray([Mixed::from(123)]);

[$newQueue, $element] = $queue->dequeue();

$element            // (Mixed) 123
$queue->Length()    // 1
$newQueue->Length() // 0
```

#### public function head(): mixed

Returns (peeks) the next element to be dequeue.

```
$queue = MixedQueue::fromArray([Mixed::from(123), Mixed::from(456)]);

$element = $queue->tail;

$element            // (Mixed) 456
$queue->Length()    // 2
```

#### public function length(): int
    
Alias to [count()](#public-function-count-int).

#### public function tail(): mixed

Returns (peeks) the last enqueued element.

```
$queue = MixedQueue::fromArray([Mixed::from(123), Mixed::from(456)]);

$element = $queue->tail;

$element            // (Mixed) 123
$queue->Length()    // 2
```
