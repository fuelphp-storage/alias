<?php

use FuelPHP\Alias\Manager;

class AliasTests extends PHPUnit_Framework_TestCase
{
	public function testLiteral()
	{
		$manager = new Manager();
		$manager->alias('Test', 'FuelPHP\Alias\Dummy');

		$this->assertTrue($manager->resolve('Test'));
		$this->assertFalse($manager->resolve('Unknown'));
	}

	public function testMatchedLiteral()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'Tester\*' => 'FuelPHP\Alias\Dummy',
		));

		$this->assertTrue($manager->resolve('Tester\ThisClass'));
		$this->assertFalse($manager->resolve('Unknown\ThisClass'));
	}

	public function testMatchedReplacement()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'Test\*' => 'FuelPHP\Alias\$1',
		));

		$this->assertTrue($manager->resolve('Test\Dummy'));
		$this->assertFalse($manager->resolve('Test\Unknown'));
	}

	public function testNonExistingResolving()
	{
		$manager = new Manager;
		$manager->alias('ThisClass', 'ToSomethingThatDoesntExist');
		$this->assertFalse($manager->resolve('ThisClass'));
	}

	public function testRemoveResolver()
	{
		$manager = new Manager();
		$manager->alias(array(
			'Resolvable' => 'FuelPHP\Alias\Dummy',
			'ResolvableTwo' => 'FuelPHP\Alias\Dummy',
			'ResolvableThree' => 'FuelPHP\Alias\Dummy',
			'ResolvableFour' => 'FuelPHP\Alias\Dummy',
		));
		$this->assertTrue($manager->resolve('Resolvable'));
		$manager->removeAlias('ResolvableTwo');
		$this->assertFalse($manager->resolve('ResolvableTwo'));
		$manager->removeAlias('ResolvableThree');
		$this->assertFalse($manager->resolve('ResolvableThree'));
		$manager->removeAlias('ResolvableFour', 'FuelPHP\Alias\Dummy');
		$this->assertFalse($manager->resolve('ResolvableFour'));
	}

	public function testRemovePatternResolver()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'PatternResolvable' => 'FuelPHP\Alias\Dummy',
			'PatternResolvableTwo' => 'FuelPHP\Alias\Dummy',
			'PatternResolvableThree' => 'FuelPHP\Alias\Dummy',
			'PatternResolvableFour' => 'FuelPHP\Alias\Dummy',
		));
		$this->assertTrue($manager->resolve('PatternResolvable'));
		$manager->removeAliasPattern('PatternResolvableTwo');
		$this->assertFalse($manager->resolve('PatternResolvableTwo'));
		$manager->removeAliasPattern('PatternResolvableThree');
		$this->assertFalse($manager->resolve('PatternResolvableThree'));
		$manager->removeAliasPattern('PatternResolvableFour', 'FuelPHP\Alias\Dummy');
		$this->assertFalse($manager->resolve('PatternResolvableFour'));
	}

	public function testResolveAutoloader()
	{
		$manager = new Manager();
		$manager->alias(array(
			'Autoloaded\Dummy' => 'FuelPHP\Alias\Dummy',
			'Second\Autoloaded\Dummy' => 'FuelPHP\Alias\Dummy',
			'Third\Autoloaded\Dummy' => 'FuelPHP\Alias\Dummy',
		));
		$this->assertFalse(class_exists('Autoloaded\Dummy', true));
		$this->assertTrue($manager->resolve('Autoloaded\Dummy'));
		$manager->register();
		$this->assertTrue(class_exists('Second\Autoloaded\Dummy', true));
		$manager->unregister();
		$this->assertFalse(class_exists('Third\Autoloaded\Dummy', true));
	}

	public function testStopRecursion()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'*\*' => '$2\\$1',
		));
		$manager->aliasPattern('*', '$1');
		$manager->register();
		$this->assertFalse($manager->resolve('Unre\Solvable'));
		$this->assertFalse($manager->resolve('Unresolvable'));
	}

	public function testTestNamespaceAliasing()
	{
		$manager = new Manager();

		$manager->aliasNamespace('FuelPHP\\Alias', '');
		$manager->aliasNamespace('Some\\Other\\Space', 'Check\\ItOut');
		$manager->aliasNamespace('Some\\Space', '');
		$manager->removeNamespaceAlias('Some\\Space');
		$this->assertTrue($manager->resolve('NsDummy'));
		$this->assertTrue($manager->resolve('Check\\ItOut\\AnotherDummy'));
		$this->assertFalse($manager->resolve('OtherDummy'));
	}

}