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
 * @version    1.0.0
 * @author     Antonio Carlos Ribeiro @ PragmaRX
 * @license    BSD License (3-clause)
 * @copyright  (c) 2013, PragmaRX
 * @link       http://pragmarx.com
 */

namespace PragmaRX\Support;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

use Illuminate\Foundation\AliasLoader as IlluminateAliasLoader;

use App;

abstract class ServiceProvider extends IlluminateServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * This variable will be built at runtime using child variables
	 *
	 * @var string
	 */
	protected $packageNamespace;

	protected $packageVendor;

	protected $packageName;

	protected $packageNameCapitalized;

	/**
	 * Gets the root directory of the child ServiceProvider
	 *
	 * @return string
	 */
	protected function getRootDirectory()
	{
		return __DIR__.'/../..';
	}

	/**
	 * Boot for the child ServiceProvider
	 *
	 * @return void
	 */
	protected function wakeUp()
	{

	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ( $this->app['config']->get($this->packageNamespace.'::create_'.$this->packageName.'_alias') )
		{
			$this->registerServiceAlias(
				$this->getConfig($this->packageName.'_alias'),
				'PragmaRX\\'.$this->packageNameCapitalized.'\Vendor\Laravel\Facade'
			);
		}

		$this->wakeUp();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerConfig();

		$this->registerFilesystem();
	}

	/**
	 * Get a configuration value
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getConfig($key)
	{
		return $this->app['config']->get($this->packageNamespace.'::'.$key);
	}

	/**
	 * Register the configuration object
	 *
	 * @return void
	 */
	private function registerConfig()
	{
		/// Fix a possible Laravel Bug
		App::register('Illuminate\Translation\TranslationServiceProvider');

		$this->packageNamespace = "$this->packageVendor/$this->packageName";

		$this->app['config']->package($this->packageNamespace, __DIR__.'/../../config', $this->packageNamespace);

		$this->package($this->packageNamespace, $this->packageNamespace, $this->getRootDirectory());

		$this->app[$this->packageName.'.config'] = $this->app->share(function($app)
		{
			return new Config($app['config'], $this->packageNamespace);
		});
	}

	/**
	 * Register the Filesystem driver used by the child ServiceProvider
	 *
	 * @return void
	 */
	private function registerFileSystem()
	{
		$this->app[$this->packageName.'.fileSystem'] = $this->app->share(function($app)
		{
			return new Filesystem;
		});
	}

	public function loadFacade($name, $class)
	{
		IlluminateAliasLoader::getInstance()->alias($name, $class);
	}

	public function registerServiceAlias($name, $class)
	{
		IlluminateAliasLoader::getInstance()->alias($name, $class);
	}

	public function registerServiceProvider($class)
	{
		$this->app->register($class);
	}
}
