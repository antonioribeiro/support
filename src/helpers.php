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