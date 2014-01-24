<?php namespace PragmaRX\Support\Vendor\Laravel;
 
use PragmaRX\Support\Support;

use PragmaRX\Support\Support\Config;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Foundation\AliasLoader as IlluminateAliasLoader;

class ServiceProvider extends IlluminateServiceProvider {

    const PACKAGE_NAMESPACE = 'pragmarx/support';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package(self::PACKAGE_NAMESPACE, self::PACKAGE_NAMESPACE, __DIR__.'/../..');

        if( $this->app['config']->get(self::PACKAGE_NAMESPACE.'::create_support_alias') )
        {
            IlluminateAliasLoader::getInstance()->alias(
                                                            $this->getConfig('support_alias'),
                                                            'PragmaRX\Support\Vendor\Laravel\Facade'
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

        $this->registerSupport();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('support');
    }

    /**
     * Takes all the components of Support and glues them
     * together to create Support.
     *
     * @return void
     */
    private function registerSupport()
    {
        $this->app['support'] = $this->app->share(function($app)
        {
            $app['support.loaded'] = true;

            return new Support($app['support.config']);
        });
    }

    public function registerConfig()
    {
        $this->app['support.config'] = $this->app->share(function($app)
        {
            return new Config($app['config'], self::PACKAGE_NAMESPACE);
        });
    }

    private function wakeUp()
    {
        $this->app['support']->boot();
    }

    private function getConfig($key)
    {
        return $this->app['config']->get(self::PACKAGE_NAMESPACE.'::'.$key);
    }

}
