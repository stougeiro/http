<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Contract\Http\ResponseInterface;
    use STDW\Http\Response;


    class TextResponse extends Response
    {
        /**
         * @param int $status 
         * @param array<string, string> $headers 
         * @param null|string $body 
         * @return void 
         */
        public function __construct(int $status = 200, array $headers = [], ?string $body = ' ')
        {
            parent::__construct($status, $headers, $body);
        }

        /**
         * @param null|string $body
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withBody(?string $body): ResponseInterface
        {
            if (is_null($body) || $body === '') {
                throw new InvalidArgumentException("TextResponse requires a non-empty body");
            }

            $this->body = $body;

            return $this;
        }

        /** @return void
         */
        public function send(): void
        {
            /** Send
             */

            $this->withHeader('Content-Type', 'text/plain; charset=utf-8');

            parent::send();
        }
    }
