<?php

use FuelPHP\Alias\Manager;

class AliasTests extends PHPUnit_Framework_TestCase
{
	public function testLiteral()
	{
		$manager = new Manager();
		$manager->alias('Test','FuelPHP\Alias\Dummy');

		$this->assertTrue($manager->resolve('Test'));
		$this->assertFalse($manager->resolve('Unknown'));
	}

	public function testMatchedLiteral()
	{
		$manager = new Manager();
		$manager->alias(array(
			'Tester\*' => 'FuelPHP\Alias\Dummy',
		));

		$this->assertTrue($manager->resolve('Tester\ThisClass'));
		$this->assertFalse($manager->resolve('Unknown\ThisClass'));
	}

	public function testMatchedReplacement()
	{
		$manager = new Manager();
		$manager->alias(array(
			'Test\*' => 'FuelPHP\Alias\$1',
		));

		$this->assertTrue($manager->resolve('Test\Dummy'));
		$this->assertFalse($manager->resolve('Test\Unknown'));
	}

	public function testCallable()
	{
		$manager = new Manager();
		$manager->alias(array(
			'Tester' => function(){ return 'FuelPHP\Alias\Dummy'; },
		));

		$this->assertTrue($manager->resolve('Tester'));
		$this->assertFalse($manager->resolve('Unknown'));
	}

	public function testMatchedCallable()
	{
		$manager = new Manager();
		$manager->alias(array(
			'Tester\*' => function(){ return 'FuelPHP\Alias\Dummy'; },
		));

		$this->assertTrue($manager->resolve('Tester\ThisOtherClass'));
		$this->assertFalse($manager->resolve('Unknown\ThisClass'));
	}

	public function testCallableSegments()
	{
		$manager = new Manager();
		$manager->alias(array(
			'OtherNamespace\*' => function ($segments) {
				return 'FuelPHP\Alias\\'.reset($segments);
			},
		));

		$this->assertTrue($manager->resolve('OtherNamespace\Dummy'));
		$this->assertFalse($manager->resolve('Test\Unknown'));
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
		$manager->removeAlias('ResolvableTwo', 'should not remove resolver');
		$this->assertTrue($manager->resolve('ResolvableTwo'));
		$manager->removeAlias('ResolvableThree');
		$this->assertFalse($manager->resolve('ResolvableThree'));
		$manager->removeAlias('ResolvableFour', 'FuelPHP\Alias\Dummy');
		$this->assertFalse($manager->resolve('ResolvableFour'));
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
		$manager->alias(array(
			'*\*' => '$2\\$1',
			'*' => '$1',
		));
		$manager->register();
		$this->assertFalse($manager->resolve('Unre\Solvable'));
		$this->assertFalse($manager->resolve('Unresolvable'));
	}

	public function testTestNamespaceALiasing()
	{
		$manager = new Manager();

		$manager->aliasNamespace('FuelPHP\\Alias', '');
		$manager->aliasNamespace('Some\\Other\\Space', 'Check\\ItOut');
		$manager->aliasNamespace('Some\\Space', '');
		$manager->removeNamespaceAlias('Some\\Space', '');
		$this->assertTrue($manager->resolve('Dummy'));
		$this->assertTrue($manager->resolve('Check\\ItOut\\AnotherDummy'));
		$this->assertFalse($manager->resolve('OtherDummy'));
	}

}