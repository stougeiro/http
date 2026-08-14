<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Contract\Http\ResponseInterface;
    use STDW\Http\Response;


    class JsonResponse extends Response
    {
        /**
         * @param null|string $body
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withBody(?string $body): ResponseInterface
        {
            if (is_null($body) || $body === '') {
                throw new InvalidArgumentException("JsonResponse requires a non-empty body");
            }

            json_decode($body);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException("Invalid JSON body: ". json_last_error_msg());
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

            $this->withHeader('Content-Type', 'application/json; charset=utf-8');

            parent::send();
        }
    }
