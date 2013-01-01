<?php

namespace FuelPHP\Alias;

class Manager
{
	/**
	 * @var  array  $aliases  class aliases
	 */
	protected $aliases = array();

	/**
	 * @var  FuelPHP\Alias\Cache  $cache  cache handler
	 */
	protected $cache;

	/**
	 * @var  array  current classes being resolved
	 */
	protected $resolving = array();

	/**
	 * Register a class alias
	 *
	 * @param   mixed  $pattern      class pattern or array of aliases
	 * @param   mixed  $translation  class translation
	 * @return  $this
	 */
	public function alias($pattern, $translation = null)
	{
		if ( ! is_array($pattern))
		{
			$pattern = array($pattern => $translation);
		}

		foreach ($pattern as $p => $resolver)
		{
			if ( ! ($resolver instanceof Resolver))
			{
				$resolver = new Resolver($p, $resolver);
			}

			$this->aliases[$p] = $resolver;
		}

		return $this;
	}

	/**
	 * Remove an alias
	 *
	 * @param   string  $pattern  pattern to remove
	 * @param   $translation  optional translation to match
	 * @return  $this
	 */
	public function removeAlias($pattern, $translation = null)
	{
		foreach (array_keys($this->aliases) as $i)
		{
			if ($this->aliases[$i]->matches($pattern, $translation))
			{
				unset($this->aliases[$i]);
			}
		}

		return $this;
	}

	/**
	 * Finds the resolver for an alias
	 *
	 * @param   string   $alias  class alias
	 * @return  mixed
	 */
	protected function resolveAlias($alias)
	{
		if (isset($this->aliases[$alias]) and $class = $this->aliases[$alias]->resolve($alias))
		{
			return $class;
		}

		foreach ($this->aliases as $resolver)
		{
			if ($class = $resolver->resolve($alias)) return $class;
		}
	}

	/**
	 * Resolves an alias.
	 *
	 * @param   string   $alias  class alias
	 * @return  boolean  wether the class is resolved/loaded
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

		// Find the resolver
		if ( ! ($class = $this->resolveAlias($alias)))
		{
			return false;
		}
		// Remove the resolving class
		array_pop($this->resolving);

		// Create the actual alias
		class_alias($class, $alias);

		if ($this->cache)
		{
			$this->cache->cache($class, $alias);
		}

		return true;
	}

	/**
	 * Set and load alias cache.
	 *
	 * @param   FuelPHP\Alias\Cache|string  $cache   cache handler or cache path
	 * @param   string                   $format  cache format
	 * @return  $this
	 */
	public function cache($cache, $format = null)
	{
		if ( ! $cache instanceof Cache)
		{
			$cache = new Cache($cache);
		}

		if ($format)
		{
			$cache->format($format);
		}

		$cache->setManager($this)
			->load()
			->register();

		$this->cache = $cache;

		return $this;
	}

	/**
	 * Registers the autoloader function.
	 *
	 * @param   bool    $placement  register placement, append or prepend
	 * @return  $this
	 */
	public function register($placement = 'prepend')
	{
		$prepend = ($placement === 'append') ? false : true;
		spl_autoload_register(array($this, 'resolve'), true, $prepend);

		return $this;
	}

	/**
	 * Unregisters the autoloader function.
	 *
	 * @return  $this
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'resolve'));

		return $this;
	}
}