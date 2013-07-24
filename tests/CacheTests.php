<?php

use Fuel\Alias\Cache;
use Fuel\Alias\Manager;
use Mockery as m;

class CacheTests extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		if (file_exists(__DIR__.'/../cache/cache.php'))
		{
			unlink(__DIR__.'/../cache/cache.php');
		}
	}

	public function tearDown()
	{
		m::close();
		if (file_exists(__DIR__.'/../cache'))
		{
			if (file_exists(__DIR__.'/../cache/cache.php'))
			{
				unlink(__DIR__.'/../cache/cache.php');
			}

			rmdir(__DIR__.'/../cache');
		}
	}

	/**
	 * @expectedException  InvalidArgumentException
	 */
	public function testCacheInvalidFormat()
	{
		$cache = new Cache('/unknown/location.php');
		$cache->register();
		$cache->format('unknown');
	}

	public function testRegisterCache()
	{
		$manager = new Manager;
		$manager->alias('My\Alias', 'Fuel\Alias\Dummy');
		$manager->aliasNamespace('Fuel\\Alias', '');
		$cache = m::mock('Fuel\\Alias\\Cache', function ($cache) use ($manager){
			$cache->shouldReceive('format')
				->with('unwind')
				->andReturn($cache)
				->shouldReceive('setManager')
				->with($manager)
				->andReturn($cache)
				->shouldReceive('load')
				->andReturn($cache)
				->shouldReceive('register')
				->andReturn($cache)
				->shouldReceive('cache')
				->with('Fuel\Alias\Dummy', 'My\Alias')
				->shouldReceive('cache')
				->with('Fuel\\Alias\\CacheDummy', 'CacheDummy');
		});
		$manager->cache($cache, 'unwind');
		$manager->resolve('My\Alias');
		$manager->resolve('CacheDummy');
	}

	public function testCacheLoad()
	{
		$cache = new Cache(__DIR__.'/../resources/cache.test.php');
		$manager = m::mock('Fuel\Alias\Manager');
		$manager->shouldReceive('alias')
			->with(array(
				'MyObject' => 'Dummy',
				'Dummy' => 'Pref\Dummy',
				'Pref\Dummy' => 'Prefixed\Dummy\Wooo',
				'Prefixed\Dummy\Wooo' => 'Dummy\Wooo\Prefixed',
				'Dummy\Wooo\Prefixed' => 'Wooo\Prefixed\Dummy',
			));
		$cache->setManager($manager);
		$cache->load();
	}

	public function testDelete()
	{
		copy(__DIR__.'/../resources/cache.test.php', __DIR__.'/../resources/cache.copy.php');
		$this->assertTrue(file_exists(__DIR__.'/../resources/cache.copy.php'));
		$cache = new Cache(__DIR__.'/../resources/cache.copy.php');
		$cache->delete();
		$this->assertFalse(file_exists(__DIR__.'/../resources/cache.copy.php'));
	}

	public function testGenerateAlias()
	{
		$cache = new Cache(__DIR__.'/../cache/cache.php');
		$cache->cache('One', 'Other');
		$cache->update();
		$contents = file_get_contents(__DIR__.'/../cache/cache.php');
		$this->assertContains('class_alias(\'One\', \'Other\');', $contents);
	}

	public function testGenerateClass()
	{
		$cache = new Cache(__DIR__.'/../cache/cache');
		$cache->format('class');
		$cache->cache('Namespaced\One', 'Other\Other');
		$cache->update();
		$contents = file_get_contents(__DIR__.'/../cache/cache.php');
		$this->assertContains('class Other extends \\Namespaced\One', $contents);
		$this->assertContains('namespace Other {', $contents);
	}

	public function testGenerateUnwind()
	{
		$cache = new Cache(__DIR__.'/../cache/cache');
		$cache->format('unwind');
		$cache->cache('Namespaced\One', 'Other\Other');
		$cache->update();
		// Fire double to update
		$cache->update();
		$contents = file_get_contents(__DIR__.'/../cache/cache.php');
		$this->assertContains("'Namespaced\One' => 'Other\Other'", $contents);
		$this->assertContains('$manager->alias(array(', $contents);
	}

	public function testDeleteWithoutFile()
	{
		$cache = new Cache('/unknown/location');
		$this->assertTrue($cache->delete());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAutoCreateCacheObject()
	{
		$manager = new Manager;
		$manager->cache('/some/path.php', 'this should generate the exception from Cache format');
	}
}