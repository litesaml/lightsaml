<?php

namespace LightSaml\Event;

class MessageSent
{
    public function __construct(public string $message)
    {
    }
}
