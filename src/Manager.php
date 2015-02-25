<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias;

use Fuel\Alias\Cache\Memory;

class Manager
{
	use ObjectTester;

	/**
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * @var Resolver[]
	 */
	protected $patterns = [];

	/**
	 * @var array
	 */
	protected $namespaces = [];

	/**
	 * @var array
	 */
	protected $resolving = [];

	/**
	 * @var Cache
	 */
	protected $cache;

	/**
	 * @param Cache|null $cache
	 */
	public function __construct(Cache $cache = null)
	{
		if ($cache === null)
		{
			$cache = new Memory;
		}

		$this->cache = $cache;
		$this->aliases = $cache->get();
	}

	/**
	 * Registers a class alias
	 *
	 * @param string|string[] $from
	 * @param string|null     $to
	 */
	public function alias($from, $to = null)
	{
		if (is_array($from))
		{
			$this->aliases = array_merge($this->aliases, $from);

			return $this;
		}

		$this->aliases[$from] = $to;
		$this->cache->set($from, $to);
	}

	/**
	 * Removes an alias
	 *
	 * @param string|string[] $from
	 */
	public function removeAlias()
	{
		$from = func_get_args();

		foreach ($from as $alias)
		{
			if (isset($this->aliases[$alias]))
			{
				unset($this->aliases[$alias]);
				$this->cache->delete($alias);
			}
		}
	}

	/**
	 * Resolves a plain alias
	 *
	 * @param string $alias
	 *
	 * @return string|boolean
	 */
	public function resolveAlias($alias)
	{
		// Check if alias exists in the store and as an object
		if (isset($this->aliases[$alias]) and $this->objectExists($this->aliases[$alias], true))
		{
			return $this->aliases[$alias];
		}

		return false;
	}

	/**
	 * Registers a class alias
	 * If $pattern is an array $translation is ignored.
	 *
	 * @param string|string[] $pattern
	 * @param string|null     $translation
	 */
	public function aliasPattern($pattern, $translation = null)
	{
		if ( ! is_array($pattern))
		{
			$pattern = [$pattern => $translation];
		}

		foreach ($pattern as $patternKey => $resolver)
		{
			if ( ! $resolver instanceof Resolver)
			{
				$resolver = new Resolver($patternKey, $resolver);
			}

			$this->patterns[$patternKey] = $resolver;
		}
	}

	/**
	 * Removes an alias pattern
	 *
	 * @param string $pattern
	 * @param string $translation
	 */
	public function removeAliasPattern($pattern, $translation = null)
	{
		foreach (array_keys($this->patterns) as $patternKey)
		{
			if ($this->patterns[$patternKey]->matches($pattern, $translation))
			{
				unset($this->patterns[$patternKey]);
			}
		}
	}

	/**
	 * Resolves pattern aliases
	 *
	 * @param string $alias
	 *
	 * @return string|boolean
	 */
	protected function resolvePatternAlias($alias)
	{
		if (isset($this->patterns[$alias]) and $class = $this->patterns[$alias]->resolve($alias))
		{
			return $class;
		}

		foreach ($this->patterns as $resolver)
		{
			if ($class = $resolver->resolve($alias))
			{
				return $class;
			}
		}

		return false;
	}

	/**
	 * Adds a namespace alias
	 *
	 * @param string $from
	 * @param string $to
	 */
	public function aliasNamespace($from, $to)
	{
		$from = trim($from, '\\');
		$to = trim($to, '\\');

		$this->namespaces[] = [$from, $to];
	}

	/**
	 * Removes a namespace alias
	 *
	 * @param string $from
	 */
	public function removeNamespaceAlias()
	{
		$from = func_get_args();

		$filter = function($namespace) use ($from)
		{
			return ! in_array($namespace[0], $from);
		};

		$this->namespaces = array_filter($this->namespaces, $filter);
	}

	/**
	 * Resolves a namespace alias
	 *
	 * @param string $alias Alias
	 *
	 * @return string|boolean Class name when resolved
	 */
	public function resolveNamespaceAlias($alias)
	{
		foreach ($this->namespaces as $namespace)
		{
			list($from, $to) = $namespace;

			if ($empty = empty($to) or strpos($alias, $to) === 0)
			{
				if ( ! $empty)
				{
					$alias = substr($alias, strlen($to) + 1);
				}

				$class = $from.'\\'.$alias;
				$this->resolving[] = $class;

				if ($this->objectExists($class, true))
				{
					array_pop($this->resolving);

					return $class;
				}
			}
		}

		return false;
	}

	/**
	 * Resolves an alias
	 *
	 * @param string $alias
	 *
	 * @return boolean
	 */
	public function resolve($alias)
	{
		// Skip recursive aliases if defined
		if (in_array($alias, $this->resolving))
		{
			return false;
		}

		// Set it as the resolving class for when
		// we want to block recursive resolving
		$this->resolving[] = $alias;

		// TODO: Find a nicer way of doing this. Array of closures perhaps?
		if ($this->cache->has($alias) and $class = $this->cache->get($alias))
		{
			// If we already have the alias in the cache don't bother resolving again
		}
		elseif ($class = $this->resolveAlias($alias))
		{
			// We've got a plain alias, now
			// we can skip the others as this
			// is the most powerful one.
		}
		elseif ($class = $this->resolveNamespaceAlias($alias))
		{
			// We've got a namespace alias, we
			// can skip pattern matching.
		}
		// Lastly we'll try to resolve it through
		// pattern matching. This is the most
		// expensive match type. Caching is
		// recommended if you use this.
		elseif ( ! $class = $this->resolvePatternAlias($alias))
		{
			return false;
		}

		// Remove the resolving class
		array_pop($this->resolving);

		if ( ! $this->objectExists($class))
		{
			return false;
		}

		// Create the actual alias
		class_alias($class, $alias);

		// Make sure our alias is stored in the cache for next time
		if ( ! $this->cache->has($alias))
		{
			$this->cache->set($alias, $class);
		}

		return true;
	}

	/**
	 * Registers the autoloader function
	 *
	 * @param boolean $placement Register placement, append or prepend
	 */
	public function register($placement = 'prepend')
	{
		$prepend = ($placement === 'append') ? false : true;

		spl_autoload_register(array($this, 'resolve'), true, $prepend);
	}

	/**
	 * Unregisters the autoloader function.
	 */
	public function unregister()
	{
		spl_autoload_unregister([$this, 'resolve']);
	}
}
