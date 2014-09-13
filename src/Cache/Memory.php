<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias\Cache;

use Fuel\Alias\CacheInterface;

/**
 * Stores aliased classes in memory
 *
 * @package Fuel\Alias\Cache
 *
 * @since 2.0
 */
class Memory implements CacheInterface
{

	protected $cache = [];

	/**
	 * {@inheritdoc}
	 */
	public function has($alias)
	{
		return isset($this->cache[$alias]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($alias = null)
	{
		if ($alias === null)
		{
			return $this->cache;
		}

		if ($this->has($alias))
		{
			return $this->cache[$alias];
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($alias, $class)
	{
		$this->cache[$alias] = $class;

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete($alias)
	{
		unset($this->cache[$alias]);

		return true;
	}

}
