<?php

namespace GVinaccia\Pusher\Console;

use GVinaccia\Pusher\Client;
use GVinaccia\Pusher\Pusher;
use GVinaccia\Pusher\ServerMessageListener;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\ConnectionException;
use React\Socket\Server;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Run
 * @package GVinaccia\Pusher\Console
 */
class Run extends Command
{
    /**
     * @var string
     */
    protected $name = 'pusher:run';

    /**
     * @var Pusher
     */
    protected $pusherInstance;

    /**
     * @var int
     */
    protected $defaultPort;

    /**
     * @param Pusher $pusher
     * @param int $port
     */
    public function __construct(Pusher $pusher, $port)
    {
        parent::__construct();

        $this->pusherInstance = $pusher;
        $this->defaultPort = $port;

        $this->decorate();
    }

    public function fire()
    {
        $port = (int) $this->option('port');

        if (!$port) {
            $port = $this->defaultPort;
        }

        $loop = \App::make('pusher.eventloop');

        $serverMessageListener = \App::make('pusher.message.listerner');
        $serverMessageListener->onMessage(function($data) {
            $this->pusherInstance->onServerMessage($data);
        });

        // Set up our WebSocket server for clients wanting real-time updates
        $server = $this->setupServer($loop, $port);

        $this->line("<info>Listening on port: $port</info>");

        $loop->run();
    }

    protected function getOptions()
    {
        return [
            [
                "port",
                null,
                InputOption::VALUE_REQUIRED,
                "Port to listen on.",
                null
            ]
        ];
    }

    private function decorate()
    {
        $this->pusherInstance->getEmitter()->on('open', function (Client $client) {
            $id = $client->getId();
            $this->line("<info>client $id connected </info>");
        });


        $this->pusherInstance->getEmitter()->on("close", function (Client $client) {
            $id = $client->getId();
            $this->line("<info>client $id disconnected</info>");
        });

        $this->pusherInstance->getEmitter()->on("message", function (Client $client, $message) {
            $id = $client->getId();
            $this->line("<info>$id: $message</info>");
        });

        $this->pusherInstance->getEmitter()->on("error", function (Client $client, $exception) {
            $message = $exception->getMessage();
            $id = $client->getId();
            $this->line("
                <info>User $id encountered an exception:</info>
                <comment>$message</comment>
                <info>.</info>
            ");
        });
    }

    /**
     * @param $loop
     * @param $port
     * @return \Ratchet\Server\IoServer
     * @throws ConnectionException
     */
    private function setupServer($loop, $port)
    {
        $webSock = new Server($loop);
        $webSock->listen($port, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect

        return new IoServer(new HttpServer(new WsServer(new WampServer($this->pusherInstance))), $webSock);
    }
}
