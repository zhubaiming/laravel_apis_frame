<?php

namespace ApisFrame\Download;

class DownloadManager
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function guard()
    {

    }

    public function __call($method, $parameters)
    {
        return $this->guard()->{$method}($parameters);
    }
}
