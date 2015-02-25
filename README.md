# Fuel Alias

[![Build Status](https://img.shields.io/travis/fuelphp/alias.svg?style=flat-square)](https://travis-ci.org/fuelphp/alias)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/fuelphp/alias.svg?style=flat-square)](https://scrutinizer-ci.com/g/fuelphp/alias)
[![Quality Score](https://img.shields.io/scrutinizer/g/fuelphp/alias.svg?style=flat-square)](https://scrutinizer-ci.com/g/fuelphp/alias)
[![HHVM Status](https://img.shields.io/hhvm/fuelphp/alias.svg?style=flat-square)](http://hhvm.h4cc.de/package/fuelphp/alias)

**Library for lazy class aliasing.**


## Install

Via Composer

``` bash
$ composer require fuelphp/alias
```


## Usage

Within FuelPHP class aliases are used to provide easy access to namespaced classes and facilitate
class inheritance injection.

The package exposes an alias manager which lets you create 3 types of aliases:

* __Literal__<br/>A one-to-one translation. Class "Namespaced\\Classname" translates to "Another\\Classname".
* __Namespace__<br/>Namespace aliases allow you to alias an entire namespace with one call.
* __Replacement__<br/>A pattern is matched an through replacements a new class is generated. "Namespace\\*" maps to "Alias\\$1".

When registering the alias manager append or prepends itself to the autoload stack to act as a pre-processor or fallback. Depending on the amount of aliases and it could be beneficial to alternate between pre- or appending.

By default the manager will prepend itself to the autoloader stack.


### Basic Usage

```php
// Create a new alias manager
$manager = new Fuel\Alias\Manager;

// Register the manager
$manager->register();

// Alias one class
$manager->alias('Alias\Me', 'To\This');

// Or alias many
$manager->alias([
	'Alias\This' => 'To\Me',
	'AndAlias\This' => 'To\SomethingElse',
]);

//
```


### Namespace usage

```php
// alias to a less deep namespace
$manager->aliasNamespace('Less\Deep', 'Some\Super\Deep\Name\Space');


// alias a namespace to global
$manager->aliasNamespace('Some\Space', '');
```


### Pattern Usage

```php
$manager = new Fuel\Alias\Manager;

// Alias with wildcards
$manager->aliasPattern('Namespaced\*', 'Other\\$1');

$otherThing = new Namespaced\Thing;
```

This can result into class resolving that doesn't exists. Luckily the package is smart enough the check wether the class exists and will continue to look for the correct class if the resolved class does not exist. This is also taken into account when it comes to caching. Only resolved classes that exist will be cached.


## Contributing

Thank you for considering contribution to FuelPHP framework. Please see [CONTRIBUTING](https://github.com/fuelphp/fuelphp/blob/master/CONTRIBUTING.md) for details.


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
