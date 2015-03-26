<?php

namespace PragmaRX\Support;

use Symfony\Component\Finder\Finder as SymfonyFinder;

class Finder {

	/**
	 * Create instance of Finder
	 * 
	 * @return Symfony\Component\Finder\Finder
	 */
	public function __construct()
	{
		$this->finder = SymfonyFinder::create();
	}

	/**
	 * Use files selector
	 * 
	 * @return Symfony\Component\Finder\Finder
	 */
	public function files()
	{
		return $this->finder->files();
	}
	
	/**
	 * Use directories selector
	 * 
	 * @return Symfony\Component\Finder\Finder
	 */
	public function directories()
	{
		return $this->finder->directories();
	}

	/**
	 * Tell finder to find on a given path
	 * 
	 * @param  Finder $finder
	 * @return [type]         [description]
	 */
	public function in($path)
	{
		return $this->in($path);
	}

}
