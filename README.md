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

* [Queue](doc/queue.md)
* [Collection](doc/collection.md)


* MixedQueue
* MixedCollection


* Mixed