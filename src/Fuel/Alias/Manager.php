<?php

namespace Fuel\Alias;

class Manager
{
	/**
	 * @var  array  $aliasses  class aliasses
	 */
	protected $aliasses = array();

	/**
	 * @var  array  current classes being resolved
	 */
	protected $resolving = array();

	/**
	 * Register a class alias
	 *
	 * @param   mixed  $pattern      class pattern or array of aliasses
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

			$this->aliasses[$p] = $resolver;
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
		foreach (array_keys($this->aliasses) as $i)
		{
			if ($this->aliasses[$i]->matches($pattern, $translation))
			{
				unset($this->aliasses[$i]);
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
		if (isset($this->aliasses[$alias]) and $class = $this->aliasses[$alias]->resolve($alias))
		{
			return $class;
		}

		foreach ($this->aliasses as $resolver)
		{
			if ($class = $resolver->resolve($alias))
			{
				return $class;
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
		// Skip recursive aliasses if defined
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

		return true;
	}

	/**
	 * Registers the autoloader function.
	 *
	 * @param   bool    $prepend  wether to prepend the loader to the autoloader stack.
	 * @return  $this
	 */
	public function register($prepend = true)
	{
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