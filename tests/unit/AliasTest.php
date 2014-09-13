<?php

namespace Fuel\Alias;

use Codeception\TestCase\Test;

class AliasTest extends Test
{
	public function testLiteral()
	{
		$manager = new Manager();
		$manager->alias('Test', 'Fuel\Alias\Dummy');

		$this->assertTrue($manager->resolve('Test'));
		$this->assertFalse($manager->resolve('Unknown'));
	}

	public function testMatchedLiteral()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'Tester\*' => 'Fuel\Alias\Dummy',
		));

		$this->assertTrue($manager->resolve('Tester\ThisClass'));
		$this->assertFalse($manager->resolve('Unknown\ThisClass'));
	}

	public function testMatchedReplacement()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'Test\*' => 'Fuel\Alias\$1',
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
			'Resolvable' => 'Fuel\Alias\Dummy',
			'ResolvableTwo' => 'Fuel\Alias\Dummy',
			'ResolvableThree' => 'Fuel\Alias\Dummy',
			'ResolvableFour' => 'Fuel\Alias\Dummy',
		));
		$this->assertTrue($manager->resolve('Resolvable'));
		$manager->removeAlias('ResolvableTwo');
		$this->assertFalse($manager->resolve('ResolvableTwo'));
		$manager->removeAlias('ResolvableThree');
		$this->assertFalse($manager->resolve('ResolvableThree'));
		$manager->removeAlias('ResolvableFour', 'Fuel\Alias\Dummy');
		$this->assertFalse($manager->resolve('ResolvableFour'));
	}

	public function testRemovePatternResolver()
	{
		$manager = new Manager();
		$manager->aliasPattern(array(
			'PatternResolvable' => 'Fuel\Alias\Dummy',
			'PatternResolvableTwo' => 'Fuel\Alias\Dummy',
			'PatternResolvableThree' => 'Fuel\Alias\Dummy',
			'PatternResolvableFour' => 'Fuel\Alias\Dummy',
		));
		$this->assertTrue($manager->resolve('PatternResolvable'));
		$manager->removeAliasPattern('PatternResolvableTwo');
		$this->assertFalse($manager->resolve('PatternResolvableTwo'));
		$manager->removeAliasPattern('PatternResolvableThree');
		$this->assertFalse($manager->resolve('PatternResolvableThree'));
		$manager->removeAliasPattern('PatternResolvableFour', 'Fuel\Alias\Dummy');
		$this->assertFalse($manager->resolve('PatternResolvableFour'));
	}

	public function testResolveAutoloader()
	{
		$manager = new Manager();
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

		$manager->aliasNamespace('Fuel\\Alias', '');
		$manager->aliasNamespace('Some\\Other\\Space', 'Check\\ItOut');
		$manager->aliasNamespace('Some\\Space', '');
		$manager->removeNamespaceAlias('Some\\Space');
		$this->assertTrue($manager->resolve('NsDummy'));
		$this->assertFalse($manager->resolve('OtherDummy'));
	}

}

/**
 * Dummy classes for alias testing
 */
class Dummy {}

class NsDummy {}

require __DIR__.'/testNS.php';
