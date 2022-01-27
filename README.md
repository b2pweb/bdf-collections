# Collections

Implementation of commons collections, with java-like stream to apply transformations.

[![build](https://github.com/b2pweb/bdf-collections/actions/workflows/php.yml/badge.svg)](https://github.com/b2pweb/bdf-collections/actions/workflows/php.yml)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/b2pweb/bdf-collections/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/b2pweb/bdf-collections/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/b2pweb/bdf-collections/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/b2pweb/bdf-collections/?branch=master)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-collections.svg)](https://packagist.org/packages/b2pweb/bdf-collections)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-collections.svg)](https://packagist.org/packages/b2pweb/bdf-collections)
[![Type Coverage](https://shepherd.dev/github/b2pweb/bdf-collections/coverage.svg)](https://shepherd.dev/github/b2pweb/bdf-collections)

- [Installation](#installation-with-composer)
- [Usage](#usage)
    - [Collections](#collections-1)
        - [ArrayCollection](#arraycollection)
        - [OrderedCollection](#orderedcollection)
        - [HashSet](#hashset)
        - [HashTable](#hashtable)
    - [Streams](#streams)
        - [Usage](#usage-1)
        - [MutableArrayStream](#mutablearraystream)
    - [Optional](#optional)

# Installation with composer

```
composer require b2pweb/bdf-collections
```

# Usage

## Collections

All collections implements the `Bdf\Collection\CollectionInterface` interface.

A collection is a simple bag of elements, with a restricted set of methods :
- `add(mixed $element)` Add the element to the collection. The implementation may reject the operation, and returns false.
- `addAll(iterator $elements)` Equivalent to `foreach ($elements as $element) { $collection->add($element); }`.
- `clear()` Remove all elements.
- `replace(iterable $elements)` Clear and replace all elements.
- `empty()` Check if the collection has no elements.
- `contains($element, bool $strict = false)` Check if the collection contains the given element. If `$strict` is set to `true`, use strict comparison operator `===`.
- `forEach(callable $callback)` Iterates over all elements, using a callback.
- `toArray()` Convert the collection to an array.
- And inherited methods of `IteratorAggregate`, `Countable` and `Streamable`

The base behavior of collections is extended by other interfaces :
- `OrderedCollectionInterface` : Ensure that all elements of the collection are ordered. Add (or modify) methods :
    - `contains($element, bool $strict = false)` Perform a binary search. The complexity of the call is O(log(n)) instead of O(n) on a simple collection.
    - `search($element, bool $strict = false)` Get the element position. Works like `contains($element, bool $strict = false)` but return the position instead of `true`. The expression `$element == $collection->at($collection->search($element))` is always `true` when `$element` exists.
    - `at(int $position)` Get an element at the given position.
    - `ArrayAccess` methods, expects `offsetSet()`. Works with the position as offset.
- `SetInterface` : Ensure that the collection no not contains any duplicated elements. Add (or modify) methods :
    - `add($element)` If the collection already contains the element, will return false, ignore the operation.
    - `addAll(iterable $elements)` Return `false` is at least on element is already added.
    - `lookup($element)` Find the corresponding elements stored into the Set.
- `TableInterface` Add the key handling for modifying, or accessing elements :
    - `set($key, $value)` Set a value at the given key.
    - `get($key)` Get a value at the given key.
    - `hasKey($key)` Check if a key exists.
    - `keys()` Get all keys of the table.
    - `values()` Get all values as array. This is equivalent to `iterator_to_array($collection)`
    - `forEach(callable $callback)` Iterates over elements, but add the key as second argument of the callback.
    - `ArrayAccess` methods    

### ArrayCollection

The `Bdf\Collection\ArrayCollection` is the base implementation of `TableInterface` using an inner PHP array.
It has great performances but do not handle complex key types, or optimised search.

Use as collection :
```php
<?php
$collection = new ArrayCollection(['foo']);

$collection->contains('foo'); // true
$collection->contains('bar'); // false

$collection->add('bar');
$collection->contains('bar'); // true
$collection->remove('bar');
$collection->contains('bar'); // false

$collection->add(42);
$collection->contains('42', true); // false
$collection->contains(42, true); // true

// Print "foo 42"
$collection->forEach (function ($value) {
    echo $value, ' ';
});

// Same as above
foreach ($collection as $value) {
    echo $value, ' ';
}
```

Use as table :
```php
<?php
$table = new ArrayCollection(['foo' => 'bar']);

// Using methods
$table->contains('bar'); // true
$table->hasKey('foo'); // true
$table->get('foo'); // "bar"

$table->set('value', 42);
$table->contains(42); // true

// Using array access
isset($table['value']); // true
$table['value']; // 42

$table->values(); // ['bar', 42]
$table->keys(); // ['foo', 'value]

// Print "foo=bar value=42"
$table->forEach (function ($value, $key) {
    echo $key, '=', $value, ' ';
});

// Same as above
foreach ($table as $key => $value) {
    echo $key, '=', $value, ' ';
}
```

### OrderedCollection

Simple implementation of `OrderedCollectionInterface`. Do not sorts elements on modification, but only on access.

Usage :

```php
<?php
$collection = new OrderedCollection();

$collection->addAll([4, 9, 2, 7]);

$collection->contains(9); // true
$collection->search(4); // 1
$collection->at(2); // 7
$collection->toArray(); // [2, 4, 7, 9]

$collection->remove(7);
$collection->toArray(); // [2, 4, 9]

// Array access
$collection[0]; // 2
isset($collection[9]); // false : check the existence of the offset
isset($collection[1]); // true
$collection[] = 5; // Add the element 5
unset($collection[2]); // Remove the 3rd element (5)

// Prints 0=2 4=1 9=2
$collection->forEach(function ($element, $position) {
    echo "$position=$element ";
});

// Same as above
foreach ($collection as $position => $element) {
    echo "$position=$element ";
}

// A custom comparator can also be used
$collection = new OrderedCollection(function ($a, $b) {
    return $a->compute() - $b->compute();
});
```

### HashSet

A `SetInterface` implementation using an hash function for check the uniqueness of elements.
**Note: Unlike common HashSet implementations, like in java, only the hash code is used on comparison, and the equal operator is never used.** 

```php
<?php

$set = new HashSet();

$set->add('foo'); // true
$set->add('foo'); // false : already added
$set->contains('foo'); // true
$set->contains('not found'); // false

// Works also with array or objects
$set->add(['foo' => 'bar']);

$obj = new stdClass();
$set->add($obj);

$set->lookup(new stdClass())->get() === $obj; // Get the stored element, which is equals with the parameter
$set->loopup('not found')->empty(); // true : An empty optional is returned if the element is not found

$objectSet = HashSet::spl(); // Use spl_object_hash() as hash function

$obj1 = new stdClass();
$obj2 = new stdClass();

$objectSet->add($obj1);
$objectSet->contains($obj1); // true
$objectSet->contains($obj2); // false : not the same reference, hash is different
```

### HashTable

A more powerful and flexible implementation of `TableInterface`, using an hash function. This implementation is about 2 times slower than `ArrayCollection`.
Unlike `ArrayCollection`, complex key types are supported (like objects).

**Note: toArray() may failed if complex keys are used.**

Usage :
```php
<?php
// Use HashTable with multiple-keys indexing using array
$table = new HashTable();

$table[[123, 'aze']] = new Entity(1);
$table[[456, 'rty']] = new Entity(2);

$table[[123, 'aze']]; // Returns Entity(1)

// Use object as key
$table[new Key()] = 'value';

$table->toArray(false); // Associative array is not possible : return in form [ [key, value], ... ]

// Create a case insensitive table by registering a custom hash function
$ciTable = new HashTable('strtolower'); // Transform keys to lower case

$ciTable->set('Foo', 'bar');
$ciTable->get('FOO'); // 'bar'
```

## Streams

Streams are used to transform collections elements. The streams implements `Iterator`, and can be used on a `foreach`.
Each stream methods will return a new Stream instance :
- `map(callable $transformer)` Apply $transformer to each values of the stream.
- `mapKey(callable $function)` Apply $function to each values of the stream for generates keys.
- `filter(callable $predicate)` Filter stream elements that are rejected by the predicate.
- `distinct(callable $hashFunction = null)` Filter stream elements to get only distinct elements. A custom hash function can be used.
- `sort(callable $comparator = null, bool $preserveKeys = false)` Order stream elements.
- `concat(StreamInterface $stream, bool $preserveKeys = true)` Concatenate a new stream after the current stream.
- `flatMap(callable $transformer, bool $preserveKeys = false)` Create a stream resulting of concatenation of each elements content extracted by $transformer.
- `skip(int $count)` Skip the $count first elements of the stream.
- `limit(int $count, int $offset = 0)` Limit the number of elements of the stream.
- `forEach(callable $consumer)` Iterate over all stream elements.
- `toArray(bool $preserveKeys = true)` Aggregate the stream to an array.
- `first()` Get the first element of the stream.
- `reduce(callable $accumulator, $initial = null)` Reduce all elements of the stream into a single value.
- `collect(CollectorInterface $collector)` Collect all elements into a single value.
- `matchAll(callable $predicate)` Check if all elements of the stream match with the predicate.
- `matchOne(callable $predicate)` Check if at least one element of the stream match with the predicate.

### Usage

```php
$stream = Streams::wrap([7, 4, 9]);

// [ 10 => 8, 16 => 14, 20 => 18]
$stream
    ->sort()
    ->map(function ($element) { return $element * 2; })
    ->mapKey(function ($element) { return $element + 2; })
    ->toArray()
;
```

### MutableArrayStream

The stream `MutableArrayStream` is an implementation of `StreamInterface` for simple PHP array. Unlike other streams, all transformations are applied on the method call, and `$this` is returned instead of a new stream instance.
Reduce the overhead of the streams, for get better performances, but some methods has different behavior.

Usage :
```php
$collection = new ArrayCollection([...]);

$stream = $collection->mutableStream(); // Get a mutable stream from an ArrayCollection
$stream = new MutableArrayStream([...]); // Or creates using constructor

// And use like other streams
$stream
    ->map(...)
    ->filter(...)
    ->collect(...)
;
```

## Optional

The `Bdf\Collection\Util\Optional` is used to replace `null` value and null object. It permit to creates a simple null object. Methods :
- `filter(callable $predicate)` Filter the optional value.
- `map(callable $transformer)` Transform the element if it's present.
- `apply(callable $consumer)` Apply the consumer on the element if it's present.
- `or($value)` Get the current Optional value if it's present, or the parameter value if not present.
- `orThrows($exception = RuntimeException::class)` Get the current value if present, or throws an exception.
- `orSupply(callable $supplier)` Get the current value if present, or return the supplier result.
- `present()` Check if the Optional value is present or not.
- `get()` Get the current stored value.
- Magic methods, which delegates to the inner object, and wrap the return value into an optional

Usage :
```php
<?php

// Creates the optional
Optional::empty(); // "Null" optional
Optional::of($myValue); // Wrap $myValue into an Optional. The value must not be null
Optional::nullable($myValue); // Wrap $myValue into an Optional. The value may be null

Optional::empty()->present(); // false
Optional::of(42)->present(); // true

// Creates a simple null object
$myNullObject = Optional::nullable($person);

$myNullObject->firstName()->or('undefined'); // Call $person->firstName() if present, and get the return value, or return "undefined"
isset($myNullObject->myProp); // Check if property myProp exists into $person 

$myNullObject->stream(); // Creates a singleton or empty stream with the wrapped element.
```
