<?php

namespace LightSaml\Event;

class MessageReceived
{
    public function __construct(public string $message)
    {
    }
}
