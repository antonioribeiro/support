<?php

use PragmaRX\Support\Exceptions\EnvironmentVariableNotSet;

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

function getExecutablePath($cmd) 
{
    return trim(shell_exec("which $cmd"));
}

function commandExists($cmd) 
{
    $returnVal = trim(getExecutablePath("which $cmd"));
    
    return (empty($returnVal) ? false : true);
}

function removeTrailingSlash($string)
{
	return substr($string, -1) == '/' ? substr($string, 0, -1) : $string;
}