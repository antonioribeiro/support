<?php

use PragmaRX\Support\Exceptions\EnvironmentVariableNotSet;

if ( ! function_exists('env'))
{
	function env($variable)
	{
		$value = getenv($variable);

		if ($value == false)
		{
			throw new EnvironmentVariableNotSet("Environment variable not set: $variable");
		}

		if ($value === '(false)')
		{
			$value = false;
		}
		else
		if ($value === '(null)')
		{
			$value = null;
		}
		else
		if ($value === '(empty)')
		{
			$value = '';
		}

		return $value;
	}

}

if ( ! function_exists('getExecutablePath'))
{
	function getExecutablePath($cmd) 
	{
	    return trim(shell_exec("which $cmd"));
	}
}

if ( ! function_exists('commandExists'))
{
	function commandExists($cmd) 
	{
	    $returnVal = trim(getExecutablePath("which $cmd"));
	    
	    return (empty($returnVal) ? false : true);
	}
}

if ( ! function_exists('removeTrailingSlash'))
{
	function removeTrailingSlash($string)
	{
		return substr($string, -1) == '/' ? substr($string, 0, -1) : $string;
	}
}

if ( ! function_exists('str_contains'))
{
	function str_contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ($needle != '' && strpos($haystack, $needle) !== false) return true;
		}

		return false;
	}
}

if ( ! function_exists('is_windows'))
{
	function is_windows()
	{
		return str_contains(strtolower(php_uname()), 'windows');
	}
}

if ( ! function_exists('slash'))
{
	function slash()
	{
		return is_windows()
				? '\\'
				: '/';
	}
}
