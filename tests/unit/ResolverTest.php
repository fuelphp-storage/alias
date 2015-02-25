<?php

namespace Fuel\Alias;

class ResolverTest extends \Codeception\TestCase\Test
{
	public function testResolveWithoutRegex()
	{
		$resolver = new Resolver('pattern', 'stdClass');

		$this->assertEquals('stdClass', $resolver->resolve('pattern'));
	}

	public function testResolveWithRegex()
	{
		$resolver = new Resolver('Pattern\*', '$1');

		$this->assertEquals('stdClass', $resolver->resolve('Pattern\stdClass'));
	}

	public function testFailingResolve()
	{
		$resolver = new Resolver('pattern', 'translation');

		$this->assertFalse($resolver->resolve('other_pattern'));
		$this->assertFalse($resolver->resolve('pattern'));
	}

	public function testMatches()
	{
		$resolver = new Resolver('pattern', 'translation');

		$this->assertTrue($resolver->matches('pattern'));
		$this->assertTrue($resolver->matches('pattern', 'translation'));
		$this->assertFalse($resolver->matches('other_pattern', 'translation'));
		$this->assertFalse($resolver->matches('pattern', 'other_translation'));
		$this->assertFalse($resolver->matches('other_pattern', 'other_translation'));
		$this->assertFalse($resolver->matches('other_pattern'));
	}
}
