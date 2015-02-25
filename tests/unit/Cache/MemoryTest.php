<?php

namespace Fuel\Alias\Cache;

use Codeception\TestCase\Test;

class MemoryTest extends Test
{
	/**
	 * @var Memory
	 */
	protected $cache;

	public function _before()
	{
		$this->cache = new Memory;
	}

	public function testBasics()
	{
		$alias = 'FooBar';
		$class = 'BazBat';

		// Make sure nothing odd is happening before we start for real
		$this->assertFalse($this->cache->has($alias));

		// Set an item in the cache and fetch it back out
		$this->cache->set($alias, $class);
		$this->assertTrue($this->cache->has($alias));
		$this->assertEquals($class, $this->cache->get($alias));

		$this->assertEquals([$alias => $class], $this->cache->get());

		// Remove the cache item and check it's gone and we can no longer fetch it
		$this->cache->delete($alias);
		$this->assertFalse($this->cache->has($alias));
		$this->assertFalse($this->cache->get($alias));
		$this->assertEquals([], $this->cache->get());
	}
}
