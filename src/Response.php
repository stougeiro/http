<?php declare(strict_types=1);

    namespace STDW\Http;

    use InvalidArgumentException;

    use STDW\Http\Spec\ResponseInterface;
    use STDW\Http\Spec\CookiesTrait;
    use STDW\Http\Spec\HeadersTrait;


    class Response implements ResponseInterface
    {
        use CookiesTrait;
        use HeadersTrait;


        /** @var int
         */
        protected int $status = 200;

        /** @var null|string
         */
        protected ?string $body = null;


        /**
         * @param int $status 
         * @param array<string, string> $headers 
         * @param null|string $body 
         * @return void 
         */
        public function __construct(int $status = 200, array $headers = [], ?string $body = '')
        {
            $this->withStatus($status);

            foreach ($headers as $name => $value) {
                $this->withHeader($name, $value);
            }

            $this->withBody($body);
        }


        /** @return int 
         */
        public function getStatus(): int
        {
            return $this->status;
        }

        /**
         * @param int $code 
         * @return ResponseInterface 
         * @throws InvalidArgumentException
         */
        public function withStatus(int $code): ResponseInterface
        {
            if ($code < 100 || $code > 599) {
                throw new InvalidArgumentException("Invalid HTTP status code: {$code}");
            }

            $this->status = $code;

            return $this;
        }

        /**
         * @param string $name 
         * @param string $value 
         * @return ResponseInterface 
         */
        public function withHeader(string $name, string $value): ResponseInterface
        {
            $key = $this->normalizeHeaderName($name);
            $this->headers[$key] = $value;

            return $this;
        }

        /**
         * @param string $name 
         * @return ResponseInterface 
         */
        public function withoutHeader(string $name): ResponseInterface
        {
            $key = $this->normalizeHeaderName($name);
            unset($this->headers[$key]);

            return $this;
        }

        /** @return null|string 
         */
        public function getBody(): ?string
        {
            return $this->body;
        }

        /**
         * @param null|string $body 
         * @return ResponseInterface 
         */
        public function withBody(?string $body): ResponseInterface
        {
            $this->body = $body;

            return $this;
        }

        /** @return void 
         */
        public function send(): void
        {
            /** Status
             */
            http_response_code($this->status);

            /** Headers
             */
            foreach ($this->headers as $name => $value) {
                header($this->canonicalizeHeaderName($name) . ': ' . $value);
            }

            /**
             * Cookies 
             * 
             * @var array{
             *     name: string,
             *     value: string,
             *     options: array{
             *         expires: int,
             *         path: string,
             *         domain: string,
             *         secure: bool,
             *         httponly: bool,
             *         samesite: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
             *     }
             * } $cookie
             */
            foreach ($this->cookies as $cookie) {
                $this->sendCookie($cookie);
            }

            /** Body
             */
            if (
                   $this->statusHasNoBody($this->status)
                || is_null($this->body)
                || $this->body === ''
            ) {
                return;
            }

            echo $this->body;
        }


        /**
         * @param int $code 
         * @return bool 
         */
        protected function statusHasNoBody(int $code): bool
        {
            return (
                ($code >= 100 && $code < 200)
                || $code === 204
                || $code === 304
            );
        }
    }
