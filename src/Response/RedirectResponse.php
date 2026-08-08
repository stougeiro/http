<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Http\Spec\ResponseInterface;
    use STDW\Http\Response;


    class RedirectResponse extends Response
    {
        /** @var string
         */
        protected string $location;


        /**
         * @param string $location
         * @param int $status
         * @param array<string, string> $headers
         * @param null|string $body
         * @return void
         */
        public function __construct(string $location, int $status = 302, array $headers = [], ?string $body = '')
        {
            $this->location = $location;

            parent::__construct($status, $headers, $body);
        }


        /**
         * @param int $code
         * @return ResponseInterface
         * @throws InvalidArgumentException
         */
        public function withStatus(int $code): ResponseInterface
        {
            if ($code < 300 || $code > 399) {
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
                throw new InvalidArgumentException("Invalid body on RedirectResponse");
            }

            return $this;
        }

        /** @return void
         */
        public function send(): void
        {
            /** Send
             */

            $this->withHeader('Location', $this->location);

            parent::send();
        }
    }
