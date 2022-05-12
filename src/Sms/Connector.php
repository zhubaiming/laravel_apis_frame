<?php

namespace ApisFrame\Sms;

interface Connector
{
    public function throwException(string $message, int $code);
}
