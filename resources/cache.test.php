<?php
/**
 * Fuel Alias Caching
 *
 * This file is used to cache class aliases.
 */
$manager->alias(array(
	'MyObject' => 'Dummy',
	'Dummy' => 'Pref\Dummy',
	'Pref\Dummy' => 'Prefixed\Dummy\Wooo',
	'Prefixed\Dummy\Wooo' => 'Dummy\Wooo\Prefixed',
	'Dummy\Wooo\Prefixed' => 'Wooo\Prefixed\Dummy',
));