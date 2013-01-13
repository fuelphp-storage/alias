<?php

namespace FuelPHP\Alias;

use Closure;

class Resolver
{
	/**
	 * @var  string  $regex  pattern regex
	 */
	protected $regex;

	/**
	 * @var  string  $pattern  pattern
	 */
	protected $pattern;

	/**
	 * @var  mixed  $translation  string stranslation or callback
	 */
	protected $translation;

	/**
	 * @var  bool  $active  flag to prevent recursion when using regex's
	 */
	protected $active = false;

	/**
	 * Constructor
	 *
	 * @param  string  $pattern  pattern
	 * @param  mixed   $translation  translation
	 */
	public function __construct($pattern, $translation)
	{
		$regex = preg_quote($pattern, '#');
		$this->regex = '#^'.str_replace('\\*', '(.*)', $regex).'$#uD';
		$this->pattern = $pattern;
		$this->translation = $translation;
	}

	/**
	 * Resolve an alias
	 *
	 * @param   string        $alias  alias
	 * @return  false|string  class when found, otherwise false
	 */
	public function resolve($alias)
	{
		// Check wether the alias matches the pattern
		if ( ! preg_match($this->regex, $alias, $matches))
		{
			return false;
		}

		// Get the translation
		$translation = $this->translation;

		// Resolve closures and other callback types
		if (is_callable($translation))
		{
			array_shift($matches);
			$class = call_user_func($translation, $matches);
		}
		// Resolve plain literal translations
		elseif (strpos($translation, '$') === false)
		{
			$class = $translation;
		}
		// Resolve replacement translations
		else
		{
			// Make sure namespace seperators are escaped
			$translation = str_replace('\\', '\\\\', $translation);

			// Resolve the replacement
			$class = preg_replace($this->regex, $translation, $alias);
		}

		// Check wether the class exists
		if ( ! $class or ! class_exists($class, true))
		{
			return false;
		}

		return $class;
	}

	/**
	 * Returns wether the resolver matches a given pattern
	 * and optional translation.
	 *
	 * @param   string   $pattern  pattern
	 * @param   mixed    $translation  translation
	 * @return  boolean  wether the resolver matches
	 */
	public function  matches($pattern, $translation = null)
	{
		if ($this->pattern !== $pattern or ($translation and $translation !== $this->translation))
		{
			return false;
		}

		return true;
	}
}
