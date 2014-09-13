<?php
/**
 * @package    Fuel\Alias
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Alias;

class Manager
{

	/**
	 * @var array Class aliases
	 */
	protected $aliases = [];

	/**
	 * @var Resolver[] Class alias patterns
	 */
	protected $patterns = [];

	/**
	 * @var array Namespace aliases
	 */
	protected $namespaces = [];

	/**
	 * @var array Current classes being resolved
	 */
	protected $resolving = [];

	/**
	 * Register a class alias
	 *
	 * @param  string|string[] $from Class from or array of aliases
	 * @param  string|null     $to   Class translation
	 *
	 * @return $this
	 *
	 * @since 2.0
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
	 * @param string|string[] $from Alias to remove
	 *
	 * @return $this
	 *
	 * @since 2.0
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
	 * @param  string $alias Class alias to resolve
	 *
	 * @return string|false
	 *
	 * @since 2.0
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

		return false;
	}

	/**
	 * Register a class alias.
	 * If $pattern is an array $translation is ignored.
	 *
	 * @param string|string[] $pattern     Class pattern or array of aliases
	 * @param string|null     $translation Class translation
	 *
	 * @return $this
	 *
	 * @since 2.0
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

		return $this;
	}

	/**
	 * Remove an alias
	 *
	 * @param  string $pattern     Pattern to remove
	 * @param  string $translation Optional translation to match
	 *
	 * @return $this
	 *
	 * @since 2.0
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

		return $this;
	}

	/**
	 * Resolves pattern aliases
	 *
	 * @param string $alias Class alias
	 *
	 * @return string|false
	 *
	 * @since 2.0
	 */
	protected function resolvePatternAlias($alias)
	{
		if (isset($this->patterns[$alias]) && $class = $this->patterns[$alias]->resolve($alias))
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
	 * Alias a namespace.
	 *
	 * @param string $from From namespace
	 * @param string $to   To namespace
	 *
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function aliasNamespace($from, $to)
	{
		$from = trim($from, '\\');
		$to = trim($to, '\\');

		$this->namespaces[] = [$from, $to];

		return $this;
	}

	/**
	 * Remove a namespace alias.
	 *
	 * @param string $from From namespace
	 * @param string $to   To namespace
	 *
	 * @return $this
	 *
	 * @since 2.0
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
	 * @param string $alias Alias
	 *
	 * @return string|false Class name when resolved
	 *
	 * @since 2.0
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
					$alias = substr($alias, strlen($to)+1);
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

		return false;
	}

	/**
	 * Resolves an alias.
	 *
	 * @param string $alias Class alias
	 *
	 * @return boolean True if the alias was successful
	 *
	 * @since 2.0
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

		return true;
	}

	/**
	 * Registers the autoloader function.
	 *
	 * @param bool $placement Register placement, append or prepend
	 *
	 * @return $this
	 *
	 * @since 2.0
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
	 * @return $this
	 *
	 * @since 2.0
	 */
	public function unregister()
	{
		spl_autoload_unregister([$this, 'resolve']);

		return $this;
	}
}
