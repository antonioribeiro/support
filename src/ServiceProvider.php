<?php

namespace PragmaRX\Support;

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
	 * Is this service provider registered?
	 *
	 * @return string
	 */

	protected $registered = false;

	/**
	 * Get the ServiceProvider root directory
	 *
	 * @return string
	 */

	protected function getRootDirectory()
	{
		return $this->getPackageDir();
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
		if ( ! $this->registered)
		{
			$this->mergeConfig();

			$this->registerNamespace();

			$this->registerConfig();

			$this->registerFilesystem();

			$this->registered = true;
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
	public function getConfig($key = null)
	{
		if ( ! isLaravel5())
		{
			$key = $this->packageNamespace . ($key ? '::'.$key : '');

			return $this->app['config']->get($key);
		}

		// Waiting for https://github.com/laravel/framework/pull/7440
		// return $this->app['config']->get("{$this->packageVendor}.{$this->packageName}.config.{$key}");

		$key = $this->packageName . ($key ? '.'.$key : '');

		return $this->app['config']->get($key);
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
			$this->app->register('Illuminate\Translation\TranslationServiceProvider');

			$this->app['config']->package($this->packageNamespace, __DIR__.'/../../config', $this->packageNamespace);

			$this->package($this->packageNamespace, $this->packageNamespace, $this->getRootDirectory());
		}

		$this->app->singleton($this->packageName.'.config', function($app)
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
		$this->app->singleton($this->packageName.'.fileSystem', function($app)
		{
			return new Filesystem;
		});
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
		if (isLaravel5())
		{
			if (file_exists($configFile = $this->getRootDirectory().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'))
			{
				$this->publishes(
					[ $configFile => config_path($this->packageName.'.php') ],
					'config'
				);
			}

			if (file_exists($migrationsPath = $this->getRootDirectory().DIRECTORY_SEPARATOR.'migrations'))
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
			if (file_exists($configFile = $this->getRootDirectory().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'))
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
			if (file_exists($viewsFolder = $this->getRootDirectory() . DIRECTORY_SEPARATOR . 'views'))
			{
				$this->loadViewsFrom($viewsFolder, "{$this->packageVendor}/{$this->packageName}");
			}
		}
		else
		{
			$this->app->make('view')->addNamespace
			(
					$this->packageNamespace,
					$this->getRootDirectory().'/views'
			);
		}
	}

}
