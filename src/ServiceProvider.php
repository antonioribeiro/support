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

	protected $preRegistered = false;

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
		$this->registerNamespace();

		$this->publishConfig();

		$this->wakeUp();
	}

	/**
	 * Boot for the child ServiceProvider
	 *
	 * @return void
	 */
	protected function preRegister()
	{
		if ( ! $this->preRegistered)
		{
			$this->registerConfig();

			$this->registerFilesystem();

			$this->preRegistered = true;
		}
	}
	
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->preRegister();
	}

	/**
	 * Get a configuration value
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getConfig($key)
	{
		if ($this->laravel4())
		{
			return $this->app['config']->get($this->packageNamespace.'::'.$key);
		}

		// Waiting for https://github.com/laravel/framework/pull/7440
		// return $this->app['config']->get("{$this->packageVendor}.{$this->packageName}.config.{$key}");

		return $this->app['config']->get("{$this->packageName}.{$key}");
	}

	/**
	 * Register the configuration object
	 *
	 * @return void
	 */
	private function registerConfig()
	{
		if ($this->laravel4())
		{
			/// Fix a possible Laravel Bug
			App::register('Illuminate\Translation\TranslationServiceProvider');

			$this->app['config']->package($this->packageNamespace, __DIR__.'/../../config', $this->packageNamespace);

			$this->package($this->packageNamespace, $this->packageNamespace, $this->getRootDirectory());
		}

		$this->app[$this->packageName.'.config'] = $this->app->share(function($app)
		{
			// Waiting for https://github.com/laravel/framework/pull/7440
			// return new Config($app['config'], $this->packageNamespace . ($this->laravel4() ? '::' : '.config.'));

			return new Config($app['config'], $this->packageNamespace . ($this->laravel4() ? '::' : '.'));
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

	private function publishConfig()
	{
		// Waiting for https://github.com/laravel/framework/pull/7440
		//	$this->publishes([
		//		$this->getStubConfigPath()
		//			=> config_path($this->packageVendor.DIRECTORY_SEPARATOR.$this->packageName.DIRECTORY_SEPARATOR.'config.php'),
		//	]);

		$this->publishes([
			$this->getStubConfigPath()
				=> config_path($this->packageName.'.php'),
		]);
	}

	private function laravel4()
	{
		return $this->app->version() < '5.0.0';
	}

	private function registerNamespace()
	{
		if ($this->laravel4())
		{
			$this->packageNamespace = "$this->packageVendor/$this->packageName";
		}
		else
		{
			// Waiting for https://github.com/laravel/framework/pull/7440
			// $this->packageNamespace = "$this->packageVendor.$this->packageName";

			$this->packageNamespace = $this->packageName;
		}
	}

}
