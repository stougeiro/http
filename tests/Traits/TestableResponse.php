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
        if (
               $this->statusHasNoBody($this->getStatus())
            || is_null($this->getBody())
            || $this->getBody() === ''
        ) {
            return;
        }

        $this->sentBody[] = $this->getBody();
    }
}
