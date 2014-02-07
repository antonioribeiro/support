<?php namespace PragmaRX\Support;

class Environment {

	public static function load($file)
	{
		foreach(require $file as $key => $value) 
		{
			if ($value === false)
			{
				$value = '(false)';
			}
			else
			if ($value === null)
			{
				$value = '(null)';
			}
			else
			if (empty($value))
			{
				$value = '(empty)';
			}

		    putenv(sprintf('%s=%s', $key, $value));
		}
	}

}