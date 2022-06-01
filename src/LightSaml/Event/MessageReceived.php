<?php

namespace LightSaml\Event;

class MessageReceived
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
