<?php

use PragmaRX\Support\Environment;
use PragmaRX\Support\Debug\Dumper;
use PragmaRX\Support\IpAddress;

if ( ! function_exists('envRaise'))
{
	function envRaise($variable, $default = '#default#')
	{
		return Environment::get($variable, $default);
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

if ( ! function_exists('explodeTree'))
{
	/**
	 * Explode any single-dimensional array into a full blown tree structure,
	 * based on the delimiters found in it's keys.
	 *
	 * The following code block can be utilized by PEAR's Testing_DocTest
	 * <code>
	 * // Input //
	 * $key_files = array(
	 *   "/etc/php5" => "/etc/php5",
	 *   "/etc/php5/cli" => "/etc/php5/cli",
	 *   "/etc/php5/cli/conf.d" => "/etc/php5/cli/conf.d",
	 *   "/etc/php5/cli/php.ini" => "/etc/php5/cli/php.ini",
	 *   "/etc/php5/conf.d" => "/etc/php5/conf.d",
	 *   "/etc/php5/conf.d/mysqli.ini" => "/etc/php5/conf.d/mysqli.ini",
	 *   "/etc/php5/conf.d/curl.ini" => "/etc/php5/conf.d/curl.ini",
	 *   "/etc/php5/conf.d/snmp.ini" => "/etc/php5/conf.d/snmp.ini",
	 *   "/etc/php5/conf.d/gd.ini" => "/etc/php5/conf.d/gd.ini",
	 *   "/etc/php5/apache2" => "/etc/php5/apache2",
	 *   "/etc/php5/apache2/conf.d" => "/etc/php5/apache2/conf.d",
	 *   "/etc/php5/apache2/php.ini" => "/etc/php5/apache2/php.ini"
	 * );
	 *
	 * // Execute //
	 * $tree = explodeTree($key_files, "/", true);
	 *
	 * // Show //
	 * print_r($tree);
	 *
	 * // expects:
	 * // Array
	 * // (
	 * //    [etc] => Array
	 * //        (
	 * //            [php5] => Array
	 * //                (
	 * //                    [__base_val] => /etc/php5
	 * //                    [cli] => Array
	 * //                        (
	 * //                            [__base_val] => /etc/php5/cli
	 * //                            [conf.d] => /etc/php5/cli/conf.d
	 * //                            [php.ini] => /etc/php5/cli/php.ini
	 * //                        )
	 * //
	 * //                    [conf.d] => Array
	 * //                        (
	 * //                            [__base_val] => /etc/php5/conf.d
	 * //                            [mysqli.ini] => /etc/php5/conf.d/mysqli.ini
	 * //                            [curl.ini] => /etc/php5/conf.d/curl.ini
	 * //                            [snmp.ini] => /etc/php5/conf.d/snmp.ini
	 * //                            [gd.ini] => /etc/php5/conf.d/gd.ini
	 * //                        )
	 * //
	 * //                    [apache2] => Array
	 * //                        (
	 * //                            [__base_val] => /etc/php5/apache2
	 * //                            [conf.d] => /etc/php5/apache2/conf.d
	 * //                            [php.ini] => /etc/php5/apache2/php.ini
	 * //                        )
	 * //
	 * //                )
	 * //
	 * //        )
	 * //
	 * // )
	 * </code>
	 *
	 * @author  Kevin van Zonneveld <kevin@vanzonneveld.net>
	 * @author  Lachlan Donald
	 * @author  Takkie
	 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version   SVN: Release: $Id: explodeTree.inc.php 89 2008-09-05 20:52:48Z kevin $
	 * @link      http://kevin.vanzonneveld.net/
	 *
	 * @param array   $array
	 * @param string  $delimiter
	 * @param boolean $baseval
	 *
	 * @return array
	 */
	function explodeTree($array, $delimiter = '_', $baseval = false)
	{
	    if (!is_array($array)) return false;
	    $splitRE   = '/' . preg_quote($delimiter, '/') . '/';
	    $returnArr = array();
	    foreach ($array as $key => $val)
	    {
	        // Get parent parts and the current leaf
	        $parts  = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
	        $leafPart = array_pop($parts);

	        // Build parent structure
	        // Might be slow for really deep and large structures
	        $parentArr = &$returnArr;
	        foreach ($parts as $part)
	        {
	            if (!isset($parentArr[$part]))
	            {
	                $parentArr[$part] = array();
	            } elseif (!is_array($parentArr[$part]))
	            {
	                if ($baseval)
	                {
	                    $parentArr[$part] = array('__base_val' => $parentArr[$part]);
	                } else {
	                    $parentArr[$part] = array();
	                }
	            }
	            $parentArr = &$parentArr[$part];
	        }

	        // Add the final part to the structure
	        if (empty($parentArr[$leafPart]))
	        {
	            $parentArr[$leafPart] = $val;
	        } elseif ($baseval && is_array($parentArr[$leafPart]))
	        {
	            $parentArr[$leafPart]['__base_val'] = $val;
	        }
	    }
	    return $returnArr;
	}
}

if ( ! function_exists('array_get') && ! function_exists('app'))
{
	function array_get($array, $key, $default = null)
	{
		if (is_null($key)) return $array;

		if (isset($array[$key])) return $array[$key];

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) || ! array_key_exists($segment, $array))
			{
				return value($default);
			}

			$array = $array[$segment];
		}

		return $array;
	}
}

if ( ! function_exists('value'))
{
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
* @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if ( ! function_exists('array_pluck'))
{
	/**
	 * Pluck an array of values from an array. (Taylor Otwell)
	 *
	 * @param  array   $array
	 * @param  string  $value
	 * @param  string  $key
	 * @return array
	 */
	function array_pluck($array, $value, $key = null)
	{
		$results = array();

		foreach ($array as $item)
		{
			$itemValue = is_object($item) ? $item->{$value} : $item[$value];

			// If the key is "null", we will just append the value to the array and keep
			// looping. Otherwise we will key the array using the value of the key we
			// received from the developer. Then we'll return the final array form.
			if (is_null($key))
			{
				$results[] = $itemValue;
			}
			else
			{
				$itemKey = is_object($item) ? $item->{$key} : $item[$key];

				$results[$itemKey] = $itemValue;
			}
		}

		return $results;
	}
}

if ( ! function_exists('format_masked'))
{
	function format_masked($val, $mask, $charMask = '9')
	{
		$maskared = '';

		$k = 0;

		for ($i = 0; $i <= strlen($mask)-1; $i++)
		{
			if ($mask[$i] == $charMask)
			{
				if (isset($val[$k]))
				{
					$maskared .= $val[$k++];
				}
			}
			else
			{
				if (isset($mask[$i]))
				{
					$maskared .= $mask[$i];
				}
			}
		}

		return $maskared;
	}
}

if ( ! function_exists('d'))
{
	function d($data = '')
	{
		z($data);
	}
}

if ( ! function_exists('z'))
{
	function z()
	{
		array_map(function($x) { (new Dumper)->dump($x); }, func_get_args());

		try
		{
			if (function_exists('app'))
			{
				if (app()->bound('log'))
				{
					app()->make('log')->info($data);
				}
			}
		}
		catch(\Exception $exception)
		{

		}
	}
}

if ( ! function_exists('dd'))
{
	function dd($data)
	{
		zz($data);
	}
}

if ( ! function_exists('zz'))
{
	function zz($data)
	{
		z($data);

		die;
	}
}

/**
 * For usage check
 *
 *      http://stackoverflow.com/questions/96759/how-do-i-sort-a-multidimensional-array-in-php
 *
 */
if ( ! function_exists('make_comparer'))
{
	function make_comparer()
	{
		// Normalize criteria up front so that the comparer finds everything tidy
		$criteria = func_get_args();
		foreach ($criteria as $index => $criterion)
		{
			$criteria[$index] = is_array($criterion)
				? array_pad($criterion, 3, null)
				: array($criterion, SORT_ASC, null);
		}

		return function ($first, $second) use (&$criteria)
		{
			foreach ($criteria as $criterion)
			{
				// How will we compare this round?
				list($column, $sortOrder, $projection) = $criterion;
				$sortOrder = $sortOrder === SORT_DESC ? -1 : 1;

				// If a projection was defined project the values now
				if ($projection)
				{
					$lhs = call_user_func($projection, $first[$column]);
					$rhs = call_user_func($projection, $second[$column]);
				} else {
					$lhs = $first[$column];
					$rhs = $second[$column];
				}

				// Do the actual comparison; do not return if equal
				if ($lhs < $rhs)
				{
					return -1 * $sortOrder;
				} else if ($lhs > $rhs)
				{
					return 1 * $sortOrder;
				}
			}

			return 0; // tiebreakers exhausted, so $first == $second
		};
	}
}

if ( ! function_exists('array_insert'))
{
	function array_insert(&$array, $insert, $position = -1)
	{
		$array = array_values($array);

		$position = ($position == -1) ? (count($array)) : $position;

		if ($position != (count($array)))
		{
			$ta = $array;

			for ($i = $position; $i < (count($array)); $i++)
			{
				if (!isset($array[$i]))
				{
					die(print_r($array, 1) . "\r\nInvalid array: All keys must be numerical and in sequence.");
				}

				$tmp[$i + 1] = $array[$i];
				unset($ta[$i]);
			}

			$ta[$position] = $insert;
			$array = $ta + $tmp;
			//print_r($array);
		} else {
			$array[$position] = $insert;
		}

		//ksort($array);
		return true;
	}
}

if ( ! function_exists('is_json'))
{
	function is_json($string)
	{
		json_decode($string);

		return (json_last_error() == JSON_ERROR_NONE);
	}
}

if ( ! function_exists('is_xml'))
{
	function is_xml($string)
	{
		try
		{
			$doc = simplexml_load_string($string);
		}
		catch (\Exception $exception)
		{
			return false;
		}

		return ! empty($doc);
	}
}

if ( ! function_exists('xml_to_json'))
{
	function xml_to_json($string)
	{
		$xml = simplexml_load_string($string);

		$json = json_encode($xml);

		return json_decode($json, TRUE);
	}
}

if ( ! function_exists('studly')) {

	/**
	 * Convert a value to studly caps case.
	 *
	 * @param  string $value
	 * @return string
	 */
	function studly($value)
	{
		$value = ucwords(str_replace(array('-', '_'), ' ', $value));

		return str_replace(' ', '', $value);
	}
}

if ( ! function_exists('camel')) {
	/**
	 * Convert a value to camel case.
	 *
	 * @param  string $value
	 * @return string
	 */
	function camel($value)
	{
		return lcfirst(studly($value));
	}
}

if ( ! function_exists('snake')) {

	/**
	 * Convert a string to snake case.
	 *
	 * @param  string $value
	 * @param  string $delimiter
	 * @return string
	 */
	function snake($value, $delimiter = '_')
	{
		$replace = '$1' . $delimiter . '$2';

		return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', $replace, $value));
	}
}

if ( ! function_exists('camel')) {
	/**
	 * Convert a value to camel case.
	 *
	 * @param  string $value
	 * @return string
	 */
	function camel($value)
	{
		return lcfirst(studly($value));
	}
}

if ( ! function_exists('array_equal')) {

	function array_equal($a, $b)
	{
		$a = one_dimension_array($a);

		$b = one_dimension_array($b);

		return array_diff($a, $b) === array_diff($b, $a);
	}

}

if ( ! function_exists('one_dimension_array'))
{

	function one_dimension_array($array)
	{
		$it =  new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));

		return iterator_to_array($it, false);
	}

}

if ( ! function_exists( 'array_implode' ))
{
	function array_implode( $glue, $separator, $array )
	{
		if ( ! is_array( $array ) )
		{
			return $array;
		}

		$string = array();

		foreach ( $array as $key => $val )
		{
			if ( is_array( $val ) )
			{
				$val = multi_implode(',', $val );
			}

			$string[] = "{$key}{$glue}{$val}";
		}
		return implode( $separator, $string );
	}
}

if ( ! function_exists( 'multi_implode' ))
{
	function multi_implode($glue, $array)
	{
		if ( ! is_array( $array ) )
		{
			return $array;
		}

		$ret = '';

		foreach ($array as $item)
		{
			if (is_array($item))
			{
				$ret .= multi_implode($item, $glue) . $glue;
			}
			else
			{
				$ret .= $item . $glue;
			}
		}

		$ret = substr($ret, 0, 0-strlen($glue));

		return $ret;
	}
}

if ( ! function_exists( 'get_ipv4_range' )) {
	/**
	 *
	 *get the first ip and last ip from cidr(network id and mask length)
	 * i will integrate this function into "Rong Framework" :)
	 * @author admin@wudimei.com
	 * @param string $cidr 56.15.0.6/16 , [network id]/[mask length]
	 * @return array $ipArray = array( 0 =>"first ip of the network", 1=>"last ip of the network" );
	 *                         Each element of $ipArray's type is long int,use long2ip( $ipArray[0] ) to convert it into ip string.
	 * example:
	 * list( $long_startIp , $long_endIp) = get_ipv4_range( "56.15.0.6/16" );
	 * echo "start ip:" . long2ip( $long_startIp );
	 * echo "<br />";
	 * echo "end ip:" . long2ip( $long_endIp );
	 */

	function get_ipv4_range($cidr)
	{

		list($ip, $mask) = explode('/', $cidr);

		$maskBinStr = str_repeat("1", $mask) . str_repeat("0", 32 - $mask); //net mask binary string
		$inverseMaskBinStr = str_repeat("0", $mask) . str_repeat("1", 32 - $mask); //inverse mask

		$ipLong = ip2long($ip);
		$ipMaskLong = bindec($maskBinStr);
		$inverseIpMaskLong = bindec($inverseMaskBinStr);
		$netWork = $ipLong & $ipMaskLong;

		$start = $netWork + 1; //去掉网络号 ,ignore network ID(eg: 192.168.1.0)

		$end = ($netWork | $inverseIpMaskLong) - 1; //去掉广播地址 ignore brocast IP(eg: 192.168.1.255)
		return array($start, $end);
	}
}

if ( ! function_exists( 'ipv4_match_mask' ))
{
	function ipv4_match_mask($ip, $network)
	{
		// Determines if a network in the form of
		//   192.168.17.1/16 or
		//   127.0.0.1/255.255.255.255 or
		//   10.0.0.1
		// matches a given ip
		$ipv4_arr = explode('/', $network);

		if (count($ipv4_arr) == 1)
		{
			$ipv4_arr[1] = '255.255.255.255';
		}

		$network_long = ip2long($ipv4_arr[0]);

		$x = ip2long($ipv4_arr[1]);
		$mask =  long2ip($x) == $ipv4_arr[1] ? $x : 0xffffffff << (32 - $ipv4_arr[1]);
		$ipv4_long = ip2long($ip);

		return ($ipv4_long & $mask) == ($network_long & $mask);
	}
}

if ( ! function_exists( 'in_array_wildcard' ))
{
	function in_array_wildcard($what, $array)
	{
		foreach ($array as $pattern)
		{
			if (\Illuminate\Support\Str::is($pattern, $what))
			{
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'starts_with' ))
{
	function starts_with($haystack, $needle)
	{
		return \Illuminate\Support\Str::startsWith($haystack, $needle);
	}
}

if ( ! function_exists( 'ends_with' ))
{
	function ends_with($haystack, $needle)
	{
		return \Illuminate\Support\Str::endsWith($haystack, $needle);
	}
}

if ( ! function_exists( 'closure_dump' ))
{
	function closure_dump(Closure $c)
	{
		$str = 'function (';
		$r = new ReflectionFunction($c);
		$params = array();
		foreach ($r->getParameters() as $p)
		{
			$s = '';
			if ($p->isArray())
			{
				$s .= 'array ';
			}
			else if ($p->getClass())
			{
				$s .= $p->getClass()->name . ' ';
			}
			if ($p->isPassedByReference())
			{
				$s .= '&';
			}
			$s .= '$' . $p->name;
			if ($p->isOptional())
			{
				$s .= ' = ' . var_export($p->getDefaultValue(), TRUE);
			}
			$params [] = $s;
		}
		$str .= implode(', ', $params);
		$str .= '){' . PHP_EOL;
		$lines = file($r->getFileName());
		for ($l = $r->getStartLine(); $l < $r->getEndLine(); $l++)
		{
			$str .= $lines[$l];
		}
		return $str;
	}
}

if ( ! function_exists( 'db_listen' ))
{
    function db_listen($dump = true, $log = true)
	{
        \DB::listen(function() use ($dump, $log)
        {
            $arguments = func_get_args();

            if (is_object($arguments[0])) {
                $sql = $arguments[0]->sql;
                $bindings = $arguments[0]->bindings;
            } else {
                $sql = $arguments[0];
                $bindings = $arguments[1];
            }
            if ($dump)
            {
                var_dump($sql);
                var_dump($bindings);
            }

            if ($log)
            {
                \Log::info($sql);
                \Log::info($bindings);
            }
        });
	}
}

if ( ! function_exists( 'get_class_name_from_file' ))
{
	function get_class_name_from_file($file, $baseDir = '', $baseNamespace = '')
	{
		$class = $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);

		$class = $baseNamespace . substr($class, strlen($baseDir));

		return str_replace('/', '\\', $class);
	}
}

if ( ! function_exists( 'get_class_and_namespace' ))
{
	function get_class_and_namespace($file, $asString = false)
	{
		$fp = fopen($file, 'r');

		$class = $namespace = $buffer = '';

		$i = 0;

		while (!$class)
		{
		    if (feof($fp))
		    {
			    break;
		    }

		    $buffer .= fread($fp, 512);
		    $tokens = @token_get_all($buffer);

		    if (strpos($buffer, '{') === false)
		    {
			    continue;
		    }

		    for (;$i<count($tokens);$i++)
		    {
		        if ($tokens[$i][0] === T_NAMESPACE)
		        {
		            for ($j=$i+1;$j<count($tokens); $j++)
		            {
		                if ($tokens[$j][0] === T_STRING)
		                {
		                     $namespace .= '\\'.$tokens[$j][1];
		                }
		                elseif ($tokens[$j] === '{' || $tokens[$j] === ';')
		                {
		                     break;
		                }
		            }
		        }

		        if ($tokens[$i][0] === T_CLASS)
		        {
		            for ($j=$i+1;$j<count($tokens);$j++)
		            {
		                if ($tokens[$j] === '{')
		                {
		                    $class = $tokens[$i+2][1];
		                }
		            }
		        }
		    }
		}

		if ($namespace)
		{
			$namespace = substr($namespace, 1);
		}

		if ($asString)
		{
			return $namespace ? $namespace . '\\' . $class : $class;
		}

		return [$class, $namespace];
	}
}


if ( ! function_exists( 'flip_coin' ))
{
	function flip_coin()
	{
		return mt_rand(0, 1);
	}
}

if ( ! function_exists( 'make_path' ))
{
	function make_path($parts)
	{
		$path = implode(DIRECTORY_SEPARATOR, $parts);

		while(strpos($path, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false)
		{
			$path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $path);
		}

		return $path;
	}
}

if ( ! function_exists( 'human_readable_size' ))
{
	function human_readable_size($size, $show = null, $decimals = 0)
	{
		$units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

		if ( ! is_null($show) && ! in_array($show, $units)) {
			$show = null;
		}

		$extent = 1;

		foreach ($units as $rank)
		{
			if ((is_null($show) && ($size < $extent <<= 10)) || ($rank == $show))
			{
				break;
			}
		}

		return number_format($size / ($extent >> 10), $decimals) . $rank;
	}
}

if ( ! function_exists( 'call' ))
{
	function call($className, $method = null, $arguments = [])
	{
		if ( ! $method)
		{
			list($className, $method) = explode('::', $className);
		}

		if ( ! is_array($arguments))
		{
			$arguments = [$arguments];
		}

		return call_user_func_array([$className, $method], $arguments);
	}
}

if ( ! function_exists( 'to_carbon' ))
{
	function to_carbon($value, $alternateFormat = null, $defaultTime = null)
	{
		// If it's already a Carbon object, return it.
		if ($value instanceof Carbon\Carbon)
		{
			return $value;
		}

		// If this value is an integer, we will assume it is a UNIX timestamp's value
		// and format a Carbon object from this timestamp. This allows flexibility
		// when defining your date fields as they might be UNIX timestamps here.
		if (is_numeric($value))
		{
			return Carbon\Carbon::createFromTimestamp($value);
		}

		$value = str_replace('/', '-', $value);

		$value = str_replace('\\', '-', $value);

		// Try to convert it using strtotime().
		if (($date = strtotime($value)) !== false)
		{
			return Carbon\Carbon::createFromTimestamp($date);
		}

		// Finally, we will just assume this date is in the format passed as parameter
		// or we will try to use a default format.
		elseif ( ! $value instanceof DateTime)
		{
			$alternateFormat = $alternateFormat ?: 'Y-m-d H:i:s';

			return Carbon\Carbon::createFromFormat($alternateFormat, $value);
		}

		return Carbon\Carbon::instance($value);
	}
}

if ( ! function_exists( 'get_current_namespaces' ))
{
	function get_current_namespaces()
	{
		$namespaces = [];

		foreach(get_declared_classes() as $name)
		{
		    if (($pos = strrpos($name, '\\')) !== false)
		    {
			    $namespace = substr($name, 0, $pos);

			    $namespaces[$namespace] = $namespace;
		    }
		}

		return $namespaces;
	}
}

if ( ! function_exists( 'get_file_namespace' ))
{
	function get_file_namespace($filePath)
	{
		$namespaces = get_current_namespaces();

		include $filePath;

		$newNamespaces = get_current_namespaces();

		$diff = array_diff_assoc($newNamespaces, $namespaces);

		if  ( ! $diff)
		{
			return null;
		}

		$contents = file_get_contents($filePath);

		foreach($diff as $namespace)
		{
			if (strpos($contents, 'namespace '.$namespace) !== false)
			{
				return $namespace;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'array_permute' ))
{
    function array_permute($items, $perms = [])
    {
        if (empty($items)) {
            $return = array($perms);
        }  else {
            $return = array();
            for ($i = count($items) - 1; $i >= 0; --$i) {
                $newitems = $items;
                $newperms = $perms;
                list($foo) = array_splice($newitems, $i, 1);
                array_unshift($newperms, $foo);
                $return = array_merge($return, array_permute($newitems, $newperms));
            }
        }
        return $return;
    }
}

if ( ! function_exists( 'is_uuid' ))
{
    function is_uuid($guid)
    {
        if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', strtoupper($guid))) {
            return true;
        }

        return false;
    }
}

if ( ! function_exists( 'array_strings_generator' ))
{
    function array_strings_generator($array, $base_string)
    {
        $results = [];

        $array = [ 0 => $array ];

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        foreach ($iterator as $key => $value)
        {
            for ($i = $iterator->getDepth() - 1; $i > 0; $i--)
            {
                $results[$iterator->getSubIterator($i)->key()] = $value;
            }

            if (count($results) === count($array[0]))
            {
                $string = $base_string;

                foreach ($results as $name => $item)
                {
                    $string = str_replace($name, $item, $string);
                }

                yield $string;
            }
        }

        return null;
    }
}

if ( ! function_exists( 'git_version' ))
{
    function git_version()
    {
        exec('git describe --always',$version_mini_hash);

        exec('git rev-list HEAD | wc -l',$version_number);

        exec('git log -1',$line);

        $version['short'] = "v1.".trim($version_number[0])." - ".$version_mini_hash[0];

        $version['full'] = "v1.".trim($version_number[0]).".$version_mini_hash[0] (".str_replace('commit ','',$line[0]).")";

        return $version;
    }
}


if ( ! function_exists( 'str_to_utf8' ))
{
    function str_to_utf8($str) {
        if (mb_detect_encoding($str, 'UTF-8', true) === false) {
            $str = utf8_encode($str);
        }

        return $str;
    }
}

if ( ! function_exists( 'get_class_path' ))
{
    function get_class_path($class)
    {
        if (! class_exists($class)) {
            return null;
        }

        return dirname((new ReflectionClass($class))->getFileName());
    }
}

if ( ! function_exists( 'ipv4_in_range' ))
{
    function ipv4_in_range($ip, $range)
    {
        return IpAddress::ipv4InRange($ip, $range);
    }
}

if (! function_exists('instantiate')) {
    /**
     * Instantiate a class.
     *
     * @param $abstract
     * @param array $parameters
     * @return object
     */
    function instantiate($abstract, $parameters = [])
    {
        if (is_array($parameters) && count($parameters)) {
            $reflection = new ReflectionClass($abstract);

            return $reflection->newInstanceArgs((array) $parameters);
        }

        return app($abstract);
    }
}
