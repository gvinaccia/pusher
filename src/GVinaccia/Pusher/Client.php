<?php

namespace GVinaccia\Pusher;

class Client
{
    protected $socket;

    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param mixed $socket
     * @return $this
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
        return $this;
    }
} 