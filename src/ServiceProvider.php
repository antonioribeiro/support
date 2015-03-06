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

use App;
use Illuminate\Foundation\AliasLoader as IlluminateAliasLoader;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

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
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishFiles();

		$this->loadViews();
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
			$this->mergeConfig();

			$this->registerNamespace();

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
		if ( ! isLaravel5())
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
		if ( ! isLaravel5())
		{
			/// Fix a possible Laravel Bug
			App::register('Illuminate\Translation\TranslationServiceProvider');

			$this->app['config']->package($this->packageNamespace, __DIR__.'/../../config', $this->packageNamespace);

			$this->package($this->packageNamespace, $this->packageNamespace, $this->getRootDirectory());
		}

		$this->app[$this->packageName.'.config'] = $this->app->share(function($app)
		{
			// Waiting for https://github.com/laravel/framework/pull/7440
			// return new Config($app['config'], $this->packageNamespace . ( ! isLaravel5() ? '::' : '.config.'));

			return new Config($app['config'], $this->packageNamespace . ( ! isLaravel5() ? '::' : '.'));
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

	private function publishFiles()
	{
		// Waiting for https://github.com/laravel/framework/pull/7440
		//	$this->publishes([
		//		$this->getStubConfigPath()
		//			=> config_path($this->packageVendor.DIRECTORY_SEPARATOR.$this->packageName.DIRECTORY_SEPARATOR.'config.php'),
		//	]);

		if (isLaravel5())
		{
			if (file_exists($configFile = $this->getPackageDir().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'))
			{
				$this->publishes(
					[ $configFile => config_path($this->packageName.'.php') ],
					'config'
				);
			}

			if (file_exists($migrationsPath = $this->getPackageDir().DIRECTORY_SEPARATOR.'migrations'))
			{
				$this->publishes(
					[ $migrationsPath => base_path('database'.DIRECTORY_SEPARATOR.'migrations') ],
					'migrations'
				);
			}
		}
	}

	private function mergeConfig()
	{
		if (isLaravel5())
		{
			if (file_exists($configFile = $this->getPackageDir().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'))
			{
				$this->mergeConfigFrom(
					$configFile, $this->packageName
				);
			}
		}
	}

	private function registerNamespace()
	{
		if ( ! isLaravel5())
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

	private function loadViews()
	{
		if (isLaravel5())
		{
			if (file_exists($viewsFolder = $this->getPackageDir() . DIRECTORY_SEPARATOR . 'views'))
			{
				$this->loadViewsFrom($viewsFolder, "{$this->packageVendor}/{$this->packageName}");
			}
		}
	}

}
