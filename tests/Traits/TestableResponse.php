<?php

namespace Tests\Traits;

use STDW\Http\Response;

trait TestableResponse
{
    public array $sentStatus = [];
    public array $sentHeaders = [];
    public array $sentCookies = [];
    public array $sentBody = [];

    protected function sendStatus(): void
    {
        $this->sentStatus[] = $this->getStatus();
    }

    protected function sendHeaders(): void
    {
        $this->sentHeaders = $this->getHeaders();
    }

    protected function sendCookies(): void
    {
        $this->sentCookies = $this->getCookies();
    }

    protected function sendBody(): void
    {
        $this->sentBody[] = $this->getBody();
    }
}
