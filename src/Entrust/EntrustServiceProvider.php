<?php namespace Hoanghiep\Role;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Hoanghiep\Role
 */

use Illuminate\Support\ServiceProvider;

class EntrustServiceProvider extends ServiceProvider
{
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
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('entrust.php'),
        ]);

        // Register commands
        $this->commands('command.entrust.migration');
        
        // Register blade directives
        $this->bladeDirectives();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEntrust();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
        // Call to Entrust::hasRole
        \Blade::directive('role', function($expression) {
            return "<?php if (\\Entrust::hasRole{$expression}) : ?>";
        });
        
         \Blade::directive('elseifpermission', function($expression) {
            return "<?php elseif(\\Entrust::hasRole{$expression}) : ?>";
        });
        
        \Blade::directive('elserole', function($expression) {
            return "<?php else :  ?>";
        });


        \Blade::directive('endrole', function($expression) {
            return "<?php endif; // Entrust::hasRole ?>";
        });

        // Call to Entrust::can
        \Blade::directive('permission', function($expression) {
            return "<?php if (\\Entrust::can{$expression}) : ?>";
        });

         \Blade::directive('elseifpermission', function($expression) {
            return "<?php elseif(\\Entrust::can{$expression}) :  ?>";
        });
        
        \Blade::directive('elsepermission', function($expression) {
            return "<?php else :  ?>";
        });


        \Blade::directive('endpermission', function($expression) {
            return "<?php endif; // Entrust::can ?>";
        });

        // Call to Entrust::ability
        \Blade::directive('ability', function($expression) {
            return "<?php if (\\Entrust::ability{$expression}) : ?>";
        });

      \Blade::directive('elseifability', function($expression) {
            return "<?php elseifability(\\Entrust::ability{$expression}) :  ?>";
        });
        
        \Blade::directive('elseability', function($expression) {
            return "<?php else :  ?>";
        });


        \Blade::directive('endability', function($expression) {
            return "<?php endif; // Entrust::ability ?>";
        });
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerEntrust()
    {
        $this->app->bind('entrust', function ($app) {
            return new Entrust($app);
        });
        
        $this->app->alias('entrust', 'Hoanghiep\Role\Entrust');
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.entrust.migration', function ($app) {
            return new MigrationCommand();
        });
    }

    /**
     * Merges user's and entrust's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php', 'entrust'
        );
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.entrust.migration'
        ];
    }
}
