<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Http\Spec\ResponseInterface;
    use STDW\Http\Response;


    class RedirectResponse extends Response
    {
        /**
         * @param string $location
         * @param int $status 
         * @param array<string, string> $headers 
         * @param null|string $body 
         * @return void 
         */
        public function __construct(string $location, int $status = 302, array $headers = [], ?string $body = '')
        {
            $headers['Location'] = $location;

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
         * @param string $name 
         * @return ResponseInterface 
         * @throws InvalidArgumentException
         */
        public function withoutHeader(string $name): ResponseInterface
        {
            $key = $this->normalizeHeaderName($name);

            if ($key === 'location') {
                throw new InvalidArgumentException("Cannot remove Location header from RedirectResponse");
            }

            return parent::withoutHeader($name);
        }

        /**
         * @param null|string $body 
         * @return ResponseInterface 
         */
        public function withBody(?string $body): ResponseInterface
        {
            if ( ! empty($body)) {
                throw new InvalidArgumentException("Invalid body on RedirectResponse");
            }

            return $this;
        }
    }
