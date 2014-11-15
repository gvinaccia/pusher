<?php

namespace GVinaccia\Pusher;

use ZMQ;
use ZMQContext;

class PusherNotifier
{
    /**
     * @var ZMQContext
     */
    protected $context;

    /**
     * @var
     */
    protected $socket;

    /**
     * @var string
     */
    protected $socketName = 'pusher.notify';
    private $bindAddress;

    /**
     * @param $bindAddress
     */
    public function __construct($bindAddress)
    {
        $this->bindAddress = $bindAddress;
    }

    public function notify(array $data = [])
    {
        $this->getSocket()->send(json_encode($data));
    }

    protected function getSocket()
    {
        if (! $this->socket) {
            $this->socket = $this->getContext()->getSocket(ZMQ::SOCKET_PUSH, $this->socketName);
            $this->socket->connect($this->bindAddress);
        }

        return $this->socket;
    }

    protected function getContext()
    {
        if (! $this->context) {
            $context = new ZMQContext();
        }

        return $context;
    }
} 