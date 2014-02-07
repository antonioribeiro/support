<?php

use PragmaRX\Support\Exceptions\EnvironmentVariableNotSet;

function env($variable)
{
	if ($value = getenv($variable) == false)
	{
		throw new EnvironmentVariableNotSet("Environment variable not set: $variable");
	}
}