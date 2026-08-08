<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Http\Spec\ResponseInterface;
    use STDW\Http\Response;


    class EmptyResponse extends Response
    {
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
            if ( ! empty($body)) {
                throw new InvalidArgumentException("Invalid body on EmptyResponse");
            }

            return $this;
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
