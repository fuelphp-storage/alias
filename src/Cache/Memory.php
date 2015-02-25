<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias\Cache;

use Fuel\Alias\CacheInterface;

/**
 * Stores aliased classes in memory
 */
class Memory implements CacheInterface
{
	/**
	 * @var array
	 */
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
