<?php

class AliasTests extends PHPUnit_Framework_TestCase
{
	public function testLiteral()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias('Test','Fuel\Alias\Dummy');

		$this->assertTrue($manager->resolve('Test'));
		$this->assertFalse($manager->resolve('Unknown'));
	}

	public function testMatchedLiteral()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'Tester\*' => 'Fuel\Alias\Dummy',
		));

		$this->assertTrue($manager->resolve('Tester\ThisClass'));
		$this->assertFalse($manager->resolve('Unknown\ThisClass'));
	}

	public function testMatchedReplacement()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'Test\*' => 'Fuel\Alias\$1',
		));

		$this->assertTrue($manager->resolve('Test\Dummy'));
		$this->assertFalse($manager->resolve('Test\Unknown'));
	}

	public function testCallable()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'Tester' => function(){ return 'Fuel\Alias\Dummy'; },
		));

		$this->assertTrue($manager->resolve('Tester'));
		$this->assertFalse($manager->resolve('Unknown'));
	}

	public function testMatchedCallable()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'Tester\*' => function(){ return 'Fuel\Alias\Dummy'; },
		));

		$this->assertTrue($manager->resolve('Tester\ThisOtherClass'));
		$this->assertFalse($manager->resolve('Unknown\ThisClass'));
	}

	public function testCallableSegments()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'OtherNamespace\*' => function ($segments) {
				return 'Fuel\Alias\\'.reset($segments);
			},
		));

		$this->assertTrue($manager->resolve('OtherNamespace\Dummy'));
		$this->assertFalse($manager->resolve('Test\Unknown'));
	}

	public function testRemoveResolver()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'Resolvable' => 'Fuel\Alias\Dummy',
			'ResolvableTwo' => 'Fuel\Alias\Dummy',
			'ResolvableThree' => 'Fuel\Alias\Dummy',
			'ResolvableFour' => 'Fuel\Alias\Dummy',
		));
		$this->assertTrue($manager->resolve('Resolvable'));
		$manager->removeAlias('ResolvableTwo', 'should not remove resolver');
		$this->assertTrue($manager->resolve('ResolvableTwo'));
		$manager->removeAlias('ResolvableThree');
		$this->assertFalse($manager->resolve('ResolvableThree'));
		$manager->removeAlias('ResolvableFour', 'Fuel\Alias\Dummy');
		$this->assertFalse($manager->resolve('ResolvableFour'));
	}

	public function testResolveAutoloader()
	{
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'Autoloaded\Dummy' => 'Fuel\Alias\Dummy',
			'Second\Autoloaded\Dummy' => 'Fuel\Alias\Dummy',
			'Third\Autoloaded\Dummy' => 'Fuel\Alias\Dummy',
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
		$manager = new Fuel\Alias\Manager();
		$manager->alias(array(
			'*\*' => '$2\\$1',
			'*' => '$1',
		));
		$manager->register();
		$this->assertFalse($manager->resolve('Unre\Solvable'));
		$this->assertFalse($manager->resolve('Unresolvable'));
	}
}