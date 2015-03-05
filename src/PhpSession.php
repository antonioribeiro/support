<?php

/**
 * Part of the Support package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Support
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 *
 */

namespace PragmaRX\Support;

class PhpSession
{

	const DEFAULT_NAMESPACE = 'pragmarx/phpsession';

	private $namespace;

	public function __construct($namespace = null)
	{
	    $this->startSession();

		$this->setNamespace($namespace);
	}

	private function startSession()
	{
		if ( ! $this->isStarted())
		{
			session_start();
		}
	}

	private function isStarted()
	{
		return session_status() === PHP_SESSION_ACTIVE;
	}

	public function get($key, $namespace = null)
	{
		$session = $this->getNamespaceData($namespace);

		return isset($session[$key])
				? $session[$key]
				: null;
	}

	public function has($key, $namespace = null)
	{
		$session = $this->getNamespaceData($namespace);

		return isset($session[$key]);
	}

	public function put($key, $value, $namespace = null)
	{
		$session = $this->getNamespaceData($namespace);

		$session[$key] = $value;

		$this->setNamespaceData($namespace, $session);
	}

	public function setNamespace($namespace)
	{
		if ($namespace)
		{
			$this->namespace = $namespace;
		}
	}

	public function getId()
	{
		return session_id();
	}

	private function makeNamespace($namespace = null)
	{
		$namespace = $namespace ?: $this->namespace;

		return $namespace ?: self::DEFAULT_NAMESPACE;
	}

	/**
	 * @param $namespace
	 * @return mixed
	 */
	private function getNamespaceData($namespace)
	{
		return isset($_SESSION[$namespace = $this->makeNamespace($namespace)])
			? $_SESSION[$namespace]
			: array();
	}

	private function setNamespaceData($namespace, $value)
	{
		$_SESSION[$this->makeNamespace($namespace)] = $value;
	}

	public function regenerate($destroy = false)
	{
		session_regenerate_id($destroy);
	}

	public function status()
	{
		return session_status();
	}

}
