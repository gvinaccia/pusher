<?php

namespace GVinaccia\Pusher;

use Evenement\EventEmitterInterface;
use GVinaccia\Pusher\Client;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use SplObjectStorage;

class Pusher implements WampServerInterface
{
    /**
     * @var SplObjectStorage
     */
    protected $clients;

    /**
     * @var EventEmitterInterface
     */
    protected $emitter;

    /**
     * @var int
     */
    protected $id = 1;

    /**
     * @var array
     */
    protected $subscribedTopics;

    /**
     * @param EventEmitterInterface $emitter
     */
    public function __construct(EventEmitterInterface $emitter)
    {
        $this->clients = new SplObjectStorage();
        $this->subscribedTopics = [];
        $this->emitter = $emitter;
    }

    /**
     * @param ConnectionInterface $socket
     * @return null|Client
     */
    public function getClientBySocket(ConnectionInterface $socket)
    {
        foreach ($this->clients as $client) {
            if ($client->getSocket() === $socket) {
                return $client;
            }
        }
        return null;
    }

    /**
     * @return EventEmitterInterface
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    public function setEmitter(EventEmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    /**
     * When a new connection is opened it will be passed to this method
     * @param  ConnectionInterface $conn The socket/connection that just connected to your application
     * @throws \Exception
     */
    function onOpen(ConnectionInterface $conn)
    {
        $client = new Client();
        $client->setId($this->id++);
        $client->setSocket($conn);
        $this->clients->attach($client);
        $this->emitter->emit('open', [$client]);
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     * @param  ConnectionInterface $conn The socket/connection that is closing/closed
     * @throws \Exception
     */
    function onClose(ConnectionInterface $conn)
    {
        $client = $this->getClientBySocket($conn);

        if (! $client) return;

        $this->clients->detach($client);
        $this->emitter->emit("close", [$client]);
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
     * @param  ConnectionInterface $conn
     * @param  \Exception $e
     * @throws \Exception
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->emitter->emit('error', [$conn, $e]);
    }

    /**
     * An RPC call has been received
     * @param \Ratchet\ConnectionInterface $conn
     * @param string $id The unique ID of the RPC, required to respond to
     * @param string|\Ratchet\Wamp\Topic $topic The topic to execute the call against
     * @param array $params Call parameters received from the client
     */
    function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // TODO: Implement onCall() method.
    }

    /**
     * A request to subscribe to a topic has been made
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|\Ratchet\Wamp\Topic $topic The topic to subscribe to
     */
    function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $client = $this->getClientBySocket($conn);
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * A request to unsubscribe from a topic has been made
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|\Ratchet\Wamp\Topic $topic The topic to unsubscribe from
     */
    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|\Ratchet\Wamp\Topic $topic The topic the user has attempted to publish to
     * @param string $event Payload of the publish
     * @param array $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param array $eligible A list of session Ids the message should be send to (whitelist)
     */
    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // TODO: Implement onPublish() method.
    }


    function onServerMessage($message)
    {
        $channel = $message['channel'];

        if (!isset($this->subscribedTopics[$channel])) {
            return;
        }

        $topic = $this->subscribedTopics[$channel];

        $topic->broadcast($message['payload']);

        $this->emitter->emit('serverMessage', [$channel, $message['payload']]);
    }
}