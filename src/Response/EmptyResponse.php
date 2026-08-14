<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Contract\Http\ResponseInterface;
    use STDW\Http\Response;


    class EmptyResponse extends Response
    {
        /**
         * @param int $status 
         * @param array<string, string> $headers 
         * @param null|string $body 
         * @return void 
         */
        public function __construct(int $status = 204, array $headers = [], ?string $body = '')
        {
            parent::__construct($status, $headers, $body);
        }


        /**
         * @param int $code
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withStatus(int $code): ResponseInterface
        {
            if ($code !== 204) {
                throw new InvalidArgumentException("Invalid HTTP status code: {$code}");
            }

            $this->status = $code;

            return $this;
        }

        /**
         * @param null|string $body
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withBody(?string $body): ResponseInterface
        {
            if (is_null($body) || $body === '') {
                return $this;
            }

            throw new InvalidArgumentException("Invalid body on EmptyResponse");
        }

        /** @return void
         */
        public function send(): void
        {
            /** Send
             */

            $this->withoutHeader('content-type');
            $this->withoutHeader('content-length');
            $this->withoutHeader('transfer-encoding');
            $this->withoutHeader('content-encoding');

            parent::send();
        }
    }
