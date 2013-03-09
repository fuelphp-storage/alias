# FuelPHP\\Alias

[![Build Status](https://travis-ci.org/fuelphp/alias.png?branch=develop)](https://travis-ci.org/fuelphp/alias)

Library for lazy class aliasing.

## Synopsys

Within FuelPHP class aliases are used to provide easy access to namespaced classes and facilitate
class inheritance injection.

The package exposes an alias manager which lets you create 3 types of aliases:

* __Literal__<br/>A one-to-one translation. Class "Namespaced\\Classname" translates to "Another\\Classname".
* __Namespace__<br/>Namespace aliases allow you to alias an entire namespace with one call.
* __Replacement__<br/>A pattern is matched an through replacements a new class is generated. "Namespace\\*" maps to "Alias\\$1".

When registering the alias manager append or prepends itself to the autoload stack to act as a pre-processor or fallback. Depending on the amount of aliases and it could be beneficial to alternate between pre- or appending.

By default the manager will prepend itself to the autoloader stack.


## Basic Usage

```php
// Create a new alias manager
$manager = new FuelPHP\Alias\Manager;

// Register the manager
$manager->register();

// Alias one class
$manager->alias('Alias\Me', 'To\This');

// Or alias many
$manager->alias(array(
	'Alias\This' => 'To\Me',
	'AndAlias\This' => 'To\SomethingElse',
));

//
```

## Namespace usage

```php
$manager->aliasNamespace('Less\Deep', 'Some\Super\Deep\Name\Space');
// alias to a less deep namespace


$manager->aliasNamespace('Some\Space', '');
// alias a namespace to global
```

## Pattern Usage



```php
$manager = new FuelPHP\Alias\Manager;

// Alias with wildcards
$manager->aliasPattern('Namespaced\*', 'Other\\$1');

$Other_Thing = new Namespaced\Thing;
```

This can result into class resolving that doesn't exists. Luckily the package is smart enough the check wether the class exists and will continue to look for the correct class if the resolved class does not exist. This is also taken into account when it comes to caching. Only resolved classes that exist will be cached.

## Caching

In order to get blazing fast aliasing you can enable caching. There are three types of caching: alias, class and unwind. They're each helpful in different situations. The default is `alias` because this is what the package does originally and reduces the most processing.

All cache is file based and loaded when available when the caching is added to the manager. You can add caching to in the following manner:

```
$manager = new FuelPHP\Alias\Manager();

$manager->cache(new FuelPHP\Alias\Cache('/path/to/cache.php'));
/**
 * Via direct cache object injection. This is handy when you want
 * To implement your own caching method.
 */

$manager->cache('/path/to/cache', 'unwind');
// Note the optional .php
```

Caching doesn't contain garbage collection as aliases are meant to never expire. It should however be part of your deploy routine to delete the cache files. If you do want a cache routine, feel free to use the `FuelPHP\Alias\Cache::delete` method to remove the cache manually or implemented in your own garbage collection:

```
$cache = new FuelPHP\Alias\Cache('/path/to/cache');

// Remove the cache
$cache->delete();
```

It's important to think about what type of caching you use. Let's look at them.

### Alias Caching

Alias cashing generated a file that contains `class_alias` calls:

```
class_alias('ThisClass', 'ToThisClass`);
```

### Class Caching

Class caching will create a file with actual class extensions:

```
namespace Something { class Nested extends \Some\Other\Class {} }
```

### Unwind Caching

Unwind caching resolves all the computed aliases and loads them into the manager as plain aliases:

```
$manager->alias(array(
	'Something' => 'Something\ElseCLass',
));
```

