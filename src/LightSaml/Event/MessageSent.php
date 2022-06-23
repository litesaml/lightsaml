<?php

namespace LightSaml\Event;

class MessageSent
{
    public string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}
