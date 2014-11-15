<?php namespace GVinaccia\Pusher;

use Evenement\EventEmitter;
use GVinaccia\Pusher\Console\Run;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use React\EventLoop\Factory;

class PusherServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        $this->package('gvinaccia/pusher');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('pusher.emitter', function() {
            return new EventEmitter();
        });

        $this->app->bind('pusher.service', function() {
            return new Pusher($this->app->make('pusher.emitter'));
        });

        $this->app->bind('pusher.commands.run', function() {
            return new Run($this->app->make('pusher.service'), Config::get('gvinaccia/pusher::port'));
        });

        $this->app->bind('pusher.message.listerner', function() {
            return new ServerMessageListener($this->app->make('pusher.eventloop'), Config::get('gvinaccia/pusher::bindAddress'));
        });

        $this->app->bind('pusher.message.notifier', function() {
            return new PusherNotifier(Config::get('gvinaccia/pusher::bindAddress'));
        });

        $this->app->singleton('pusher.eventloop', function() {
            return Factory::create();
        });

        $this->commands('pusher.commands.run');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'pusher.emitter',
            'pusher.service',
            'pusher.commands.run',
            'pusher.message.notifier',
            'pusher.message.listener',
            'pusher.eventloop'
        ];
    }
}
