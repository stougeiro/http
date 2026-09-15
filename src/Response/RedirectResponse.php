<?php declare(strict_types=1);

    namespace STDW\Http\Response;

    use InvalidArgumentException;

    use STDW\Contract\Http\ResponseInterface;
    use STDW\Http\Response;


    class RedirectResponse extends Response
    {
        /** @var string
         */
        protected string $location;

        /** @var array<string>
         */
        protected const ALLOWED_SCHEMES = [
            'http', 'https', ''
        ];


        /**
         * @param string $location
         * @param int $status
         * @param array<string, string> $headers
         * @param null|string $body
         * @return void
         * @throws InvalidArgumentException
         */
        public function __construct(string $location, int $status = 302, array $headers = [], ?string $body = '')
        {
            $parsed = parse_url($location);

            if ($parsed === false) {
                throw new InvalidArgumentException("Invalid redirect URL: {$location}");
            }

            $scheme = strtolower($parsed['scheme'] ?? '');

            if (str_contains($location, ':') && empty($parsed['scheme'])) {
                throw new InvalidArgumentException("Invalid redirect URL: {$location}");
            }

            if ( ! in_array($scheme, self::ALLOWED_SCHEMES, true)) {
                throw new InvalidArgumentException("Redirect URL scheme not allowed: {$scheme}");
            }

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
            if (is_null($body) || $body === '') {
                return $this;
            }

            throw new InvalidArgumentException("Invalid body on RedirectResponse");
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
