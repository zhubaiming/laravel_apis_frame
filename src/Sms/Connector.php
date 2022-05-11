<?php

namespace ApisFrame\Sms;

interface Connector
{
    public function processResponse(array $response);

    public function record($phoneNumbers, $bizId);
}
