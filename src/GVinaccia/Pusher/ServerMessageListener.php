<?php

namespace GVinaccia\Pusher;

use React\EventLoop\LoopInterface;
use ZMQ;
use React\ZMQ\Context;

class ServerMessageListener
{
    protected $eventLoop;

    /**
     * @var Context
     */
    protected $zmqContext;

    protected $pullSocket;
    protected $bindAddress;

    public function __construct(LoopInterface $eventLoop, $bindAddress)
    {
        $this->eventLoop = $eventLoop;
        $this->zmqContext = new Context($this->eventLoop);

        $pull = $this->zmqContext->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind($bindAddress);

        $this->pullSocket = $pull;
        $this->bindAddress = $bindAddress;
    }

    public function onMessage( callable $messageHandler)
    {
        $this->pullSocket->on('message', $messageHandler);
    }
}
