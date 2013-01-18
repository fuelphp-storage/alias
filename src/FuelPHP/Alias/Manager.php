<?php

namespace FuelPHP\Alias;

class Manager
{
	/**
	 * @var  array  $aliases  class aliases
	 */
	protected $aliases = array();

	/**
	 * @var  array  $patterns  class alias patterns
	 */
	protected $patterns = array();

	/**
	 * @var  array  $namespaces  namespace aliases
	 */
	protected $namespaces = array();

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
	 * @param   mixed  $from      class from or array of aliases
	 * @param   mixed  $translation  class translation
	 * @return  $this
	 */
	public function alias($from, $to = null)
	{
		if (is_array($from))
		{
			$this->aliases = array_merge($this->aliases, $from);

			return $this;
		}

		$this->aliases[$from] = $to;

		return $this;
	}

	/**
	 * Remove an alias
	 *
	 * @param   string  $from  alias to remove
	 * @return  $this
	 */
	public function removeAlias($from)
	{
		$from = func_get_args();

		foreach ($from as $alias)
		{
			if (isset($this->aliases[$alias]))
			{
				unset($this->aliases[$alias]);
			}
		}

		return $this;
	}

	/**
	 * Resolves a plain alias
	 *
	 * @param   string   $alias  class alias
	 * @return  mixed
	 */
	public function resolveAlias($alias)
	{
		if (isset($this->aliases[$alias]))
		{
			$class = $this->aliases[$alias];

			if (class_exists($class, true))
			{
				return $class;
			}
		}
	}

	/**
	 * Register a class alias
	 *
	 * @param   mixed  $pattern      class pattern or array of aliases
	 * @param   mixed  $translation  class translation
	 * @return  $this
	 */
	public function aliasPattern($pattern, $translation = null)
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

			$this->patterns[$p] = $resolver;
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
	public function removeAliasPattern($pattern, $translation = null)
	{
		foreach (array_keys($this->patterns) as $i)
		{
			if ($this->patterns[$i]->matches($pattern, $translation))
			{
				unset($this->patterns[$i]);
			}
		}

		return $this;
	}

	/**
	 * Resolves pattern aliases
	 *
	 * @param   string   $alias  class alias
	 * @return  mixed
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
				return $class;
		}
	}

	/**
	 * Alias a namespace.
	 *
	 * @param   string  $from  from namespace
	 * @param   string  $to    to namespace
	 * @return  $this
	 */
	public function aliasNamespace($from, $to)
	{
		$from = trim($from, '\\');
		$to = trim($to, '\\');

		$this->namespaces[] = array($from, $to);

		return $this;
	}

	/**
	 * Remove a namespace alias.
	 *
	 * @param   string  $from  from namespace
	 * @param   string  $to    to namespace
	 * @return  $this
	 */
	public function removeNamespaceAlias($from)
	{
		$from = func_get_args();

		$filter = function($namespace) use ($from)
		{
			return ! in_array($namespace[0], $from);
		};



		$this->namespaces = array_filter($this->namespaces, $filter);

		return $this;
	}

	/**
	 * Resolve a namespace alias.
	 *
	 * @param   string  $alias  alias
	 * @return  string  class name when resolved
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
					$alias = substr($alias, strlen($to));
				}

				$class = $from.'\\'.$alias;
				$this->resolving[] = $class;

				if (class_exists($class, true))
				{
					array_pop($this->resolving);

					return $class;
				}
			}
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

		if ($class = $this->resolveAlias($alias))
		{
			// We've got a plain alias, now
			// we can skip the others as this
			// is the most powerfull one.
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