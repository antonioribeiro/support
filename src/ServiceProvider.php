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

    /**
     * Gets the root directory of the child ServiceProvider
     * 
     * @return string
     */
    abstract protected function getRootDirectory();

    /**
     * Boot procedure in the child ServiceProvider
     * 
     * @return void
     */
    abstract protected function wakeUp();

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->packageNamespace = "$this->packageVendor/$this->packageName".;

        $this->package($this->packageNamespace, $this->packageNamespace, $this->getRootDirectory());

        if( $this->app['config']->get($this->packageNamespace.'::create_'.$this->packageName.'_alias') )
        {
            IlluminateAliasLoader::getInstance()->alias(
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
    public function preRegister()
    {   
        $this->registerConfig();
    }

    /**
     * Register the configuration object
     * 
     * @return void
     */
    public function registerConfig()
    {
        $this->app[$this->packageName.'.config'] = $this->app->share(function($app)
        {
            return new Config($app['config'], $this->packageNamespace);
        });
    }

    /**
     * Get a configuration value
     * 
     * @param  string $key 
     * @return mixed
     */
    private function getConfig($key)
    {
        return $this->app['config']->get($this->packageNamespace.'::'.$key);
    }
}
