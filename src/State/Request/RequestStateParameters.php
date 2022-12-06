<?php

namespace LightSaml\State\Request;

final class RequestStateParameters
{
    public const ID = 'id';
    public const TYPE = 'type';
    public const TIMESTAMP = 'ts';
    public const PARTY = 'party';
    public const RELAY_STATE = 'relay_state';
    public const NAME_ID = 'name_id';
    public const NAME_ID_FORMAT = 'name_id_format';
    public const SESSION_INDEX = 'session_index';

    private function __construct()
    {
    }
}
