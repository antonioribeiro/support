<?php namespace PragmaRX\Support;
 
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
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package($this->packageNamespace, $this->packageNamespace, __DIR__.'/../..');

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

    private function wakeUp()
    {
        $this->app['helpers']->boot();
    }

    public function registerConfig()
    {
        $this->app[$this->packageName.'.config'] = $this->app->share(function($app)
        {
            return new Config($app['config'], $this->packageNamespace);
        });
    }

    private function getConfig($key)
    {
        return $this->app['config']->get($this->packageNamespace.'::'.$key);
    }
}
