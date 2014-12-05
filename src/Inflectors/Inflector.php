<?php

namespace PragmaRX\Support\Inflectors;

class Inflector {

	protected static $localizedInflectors = [
		'en' => 'PragmaRX\Support\Inflectors\En',
		'pt' => 'PragmaRX\Support\Inflectors\PtBr',
	];

	public function inflect($word, $count)
	{
		if ($count > 1)
		{
			return $this->plural($word);
		}

		return $this->singular($word);
	}

	public static function plural($word)
	{
		$inflector = static::getInflector();

		return $inflector->plural($word);
	}

    public static function singular($word)
    {
	    $inflector = static::getInflector();

	    return $inflector->singular($word);
    }

	private static function getInflector()
	{
		$inflector = static::$localizedInflectors['en'];

		if (function_exists('app'))
		{
			$locale = app()->make('translator')->getLocale();

			foreach (static::$localizedInflectors as $lang => $class)
			{
				if (starts_with($locale, $lang))
				{
					$inflector = $class;
				}
			}

		}

		return new $inflector;
	}

} 
