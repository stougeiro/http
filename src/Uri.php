<?php declare(strict_types=1);

    namespace STDW\Http;

    use STDW\Contract\Http\UriInterface;


    class Uri implements UriInterface
    {
        /** @var string
         */
        protected string $scheme = '';

        /** @var string
         */
        protected string $host = '';

        /** @var null|int
         */
        protected ?int $port = null;

        /** @var null|string
         */
        protected ?string $user = null;

        /** @var null|string
         */
        protected ?string $pass = null;

        /** @var string
         */
        protected string $path = '/';

        /** @var array<mixed>
         */
        protected array $query = [];

        /** @var null|string
         */
        protected ?string $fragment = null;


        /** @return void 
         */
        protected function __construct()
        { }

        /** @return string 
         */
        public function __toString(): string
        {
            $scheme = $this->scheme ? $this->scheme .'://' : '';
            $authority = $this->getAuthority();
            $query = $this->query ? '?'. http_build_query($this->query) : '';
            $fragment = $this->fragment ? '#'. $this->fragment : '';

            return $scheme . $authority . $this->path . $query . $fragment;
        }


        /**
         * @param string $url 
         * @return UriInterface 
         */
        public static function fromUrl(string $url): UriInterface
        {
            $parts = parse_url($url);

            $uri = new self;
            $uri->scheme = $parts['scheme'] ?? '';
            $uri->host = $parts['host'] ?? '';
            $uri->port = $parts['port'] ?? null;
            $uri->user = $parts['user'] ?? null;
            $uri->pass = $parts['pass'] ?? null;
            $uri->path = $parts['path'] ?? '/';
            $uri->fragment = $parts['fragment'] ?? null;

            if (isset($parts['query'])) {
                parse_str($parts['query'], $uri->query);
            }

            return $uri;
        }

        /**
         * @param array{
         *     scheme?: string,
         *     host?: string,
         *     port?: null|int,
         *     user?: null|string,
         *     pass?: null|string,
         *     path?: string,
         *     query?: array<string, mixed>,
         *     fragment?: null|string
         * } $data
         * @return UriInterface 
         */
        public static function fromArray(array $data): UriInterface
        {
            $uri = new self;
            $uri->scheme = $data['scheme'] ?? '';
            $uri->host = $data['host'] ?? '';
            $uri->port = $data['port'] ?? null;
            $uri->user = $data['user'] ?? null;
            $uri->pass = $data['pass'] ?? null;
            $uri->path = $data['path'] ?? '/';
            $uri->query = $data['query'] ?? [];
            $uri->fragment = $data['fragment'] ?? null;

            return $uri;
        }


        /** @return string 
         */
        public function getScheme(): string
        {
            return $this->scheme;
        }

        /** @return string 
         */
        public function getHost(): string
        {
            return $this->host;
        }

        /** @return null|int 
         */
        public function getPort(): ?int
        {
            return $this->port;
        }

        /** @return null|string 
         */
        public function getUser(): ?string
        {
            return $this->user;
        }

        /** @return null|string 
         */
        public function getPass(): ?string
        {
            return $this->pass;
        }

        /** @return string 
         */
        public function getAuthority(): string
        {
            $authority = $this->host;

            if ( ! is_null($this->user)) {
                $authority = $this->user;
                $authority .= is_null($this->pass) ? '' : ':'. $this->pass;
                $authority .= '@' . $this->host;
            }

            $authority .= is_null($this->port) ? '' : ':'. $this->port;

            return $authority;
        }

        /** @return string 
         */
        public function getPath(): string
        {
            return $this->path;
        }

        /** @return array<mixed>  
         */
        public function getQuery(): array
        {
            return $this->query;
        }

        /** @return null|string 
         */
        public function getFragment(): ?string
        {
            return $this->fragment;
        }
    }