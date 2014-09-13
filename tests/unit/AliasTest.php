<?php

namespace Fuel\Alias;

use Codeception\TestCase\Test;
use Mockery;

class AliasTest extends Test
{

	/**
	 * @var Manager
	 */
	protected $manager;

	public function _before()
	{
		$this->manager = new Manager();
	}

	public function testLiteral()
	{
		$this->manager->alias('Test', 'Fuel\Alias\Dummy');

		$this->assertTrue($this->manager->resolve('Test'));
		$this->assertFalse($this->manager->resolve('Unknown'));
	}

	public function testMatchedLiteral()
	{
		$this->manager->aliasPattern(array(
			'Tester\*' => 'Fuel\Alias\Dummy',
		));

		$this->assertTrue($this->manager->resolve('Tester\ThisClass'));
		$this->assertFalse($this->manager->resolve('Unknown\ThisClass'));
	}

	public function testMatchedReplacement()
	{
		$this->manager->aliasPattern(array(
			'Test\*' => 'Fuel\Alias\$1',
		));

		$this->assertTrue($this->manager->resolve('Test\Dummy'));
		$this->assertFalse($this->manager->resolve('Test\Unknown'));
	}

	public function testNonExistingResolving()
	{
		$this->manager->alias('ThisClass', 'ToSomethingThatDoesntExist');
		$this->assertFalse($this->manager->resolve('ThisClass'));
	}

	public function testAliasContainingTarget()
	{
		$this->manager->alias('Fake\Fuel\Alias\Dummy', 'Fuel\Alias\Dummy');
		$this->assertTrue($this->manager->resolve('Fake\Fuel\Alias\Dummy'));
	}

	public function testRemoveResolver()
	{
		$this->manager->alias(array(
			'Resolvable' => 'Fuel\Alias\Dummy',
			'ResolvableTwo' => 'Fuel\Alias\Dummy',
			'ResolvableThree' => 'Fuel\Alias\Dummy',
			'ResolvableFour' => 'Fuel\Alias\Dummy',
		));
		$this->assertTrue($this->manager->resolve('Resolvable'));
		$this->manager->removeAlias('ResolvableTwo');
		$this->assertFalse($this->manager->resolve('ResolvableTwo'));
		$this->manager->removeAlias('ResolvableThree');
		$this->assertFalse($this->manager->resolve('ResolvableThree'));
		$this->manager->removeAlias('ResolvableFour', 'Fuel\Alias\Dummy');
		$this->assertFalse($this->manager->resolve('ResolvableFour'));
	}

	public function testRemovePatternResolver()
	{
		$this->manager->aliasPattern(array(
			'PatternResolvable' => 'Fuel\Alias\Dummy',
			'PatternResolvableTwo' => 'Fuel\Alias\Dummy',
			'PatternResolvableThree' => 'Fuel\Alias\Dummy',
			'PatternResolvableFour' => 'Fuel\Alias\Dummy',
		));
		$this->assertTrue($this->manager->resolve('PatternResolvable'));
		$this->manager->removeAliasPattern('PatternResolvableTwo');
		$this->assertFalse($this->manager->resolve('PatternResolvableTwo'));
		$this->manager->removeAliasPattern('PatternResolvableThree');
		$this->assertFalse($this->manager->resolve('PatternResolvableThree'));
		$this->manager->removeAliasPattern('PatternResolvableFour', 'Fuel\Alias\Dummy');
		$this->assertFalse($this->manager->resolve('PatternResolvableFour'));
	}

	public function testResolveAutoloader()
	{
		$this->manager->alias(array(
			'Autoloaded\Dummy' => 'Fuel\Alias\Dummy',
			'Second\Autoloaded\Dummy' => 'Fuel\Alias\Dummy',
			'Third\Autoloaded\Dummy' => 'Fuel\Alias\Dummy',
		));
		$this->assertFalse(class_exists('Autoloaded\Dummy', true));
		$this->assertTrue($this->manager->resolve('Autoloaded\Dummy'));
		$this->manager->register();
		$this->assertTrue(class_exists('Second\Autoloaded\Dummy', true));
		$this->manager->unregister();
		$this->assertFalse(class_exists('Third\Autoloaded\Dummy', true));
	}

	public function testStopRecursion()
	{
		$this->manager->aliasPattern(array(
			'*\*' => '$2\\$1',
		));
		$this->manager->aliasPattern('*', '$1');
		$this->manager->register();
		$this->assertFalse($this->manager->resolve('Unre\Solvable'));
		$this->assertFalse($this->manager->resolve('Unresolvable'));
	}

	public function testTestNamespaceAliasing()
	{
		$this->manager->aliasNamespace('Fuel\\Alias', '');
		$this->manager->aliasNamespace('Some\\Other\\Space', 'Check\\ItOut');
		$this->manager->aliasNamespace('Some\\Space', '');
		$this->manager->removeNamespaceAlias('Some\\Space');
		$this->assertTrue($this->manager->resolve('NsDummy'));
		$this->assertFalse($this->manager->resolve('OtherDummy'));
	}

}

/**
 * Dummy classes for alias testing
 */
class Dummy {}

class NsDummy {}

require __DIR__.'/testNS.php';
