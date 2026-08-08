<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Http\Spec\ResponseInterface;
    use STDW\Http\Response;


    class TextResponse extends Response
    {
        /**
         * @param null|string $body
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withBody(?string $body): ResponseInterface
        {
            if (empty($body)) {
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
