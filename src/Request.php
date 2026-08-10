<?php declare(strict_types=1);

    namespace STDW\Http;

    use STDW\Contract\Http\RequestInterface;
    use STDW\Contract\Http\UriInterface;
    use STDW\Contract\Http\UploadedFileInterface;
    use STDW\Http\Spec\CookiesTrait;
    use STDW\Http\Spec\HeadersTrait;


    class Request implements RequestInterface
    {
        use HeadersTrait;
        use CookiesTrait;


        /** @var null|string
         */
        protected ?string $method = null;

        /** @var null|UriInterface
         */
        protected ?UriInterface $uri = null;

        /** @var null|array<string, string>
         */
        protected ?array $headers = null;

        /** @var null|array<string, array<string, mixed>>
         */
        protected ?array $cookies = null;

        /** @var null|array<string, mixed>
         */
        protected ?array $params = null;

        /** @var mixed
         */
        protected mixed $body = null;

        /** @var array<string, UploadedFileInterface|array<int, UploadedFileInterface>>
         */
        protected ?array $files = null;

        /** @var array<string, mixed> 
         */
        protected array $attributes;


        /** @return string 
         */
        public function getMethod(): string
        {
            return $this->method ??= $this->parseMethod();
        }

        /** @return UriInterface 
         */
        public function getUri(): UriInterface
        {
            return $this->uri ??= $this->parseUri();
        }

        /** @return array<string, string>
         */
        public function getHeaders(): array
        {
            return $this->headers ??= $this->parseHeaders();
        }

        /**
         * @param string $name 
         * @return null|string 
         */
        public function getHeader(string $name): ?string
        {
            $headers = $this->getHeaders();

            return $headers[$this->normalizeHeaderName($name)] ?? null;
        }

        /**
         * @param string $name 
         * @return bool 
         */
        public function hasHeader(string $name): bool
        {
            $headers = $this->getHeaders();

            return isset($headers[$this->normalizeHeaderName($name)]);
        }

        /** @return array<string, array<string, mixed>> 
         */
        public function getCookies(): array
        {
            return $this->cookies ??= $this->parseCookies();
        }

        /**
         * @param string $name 
         * @return null|array<string, mixed> 
         */
        public function getCookie(string $name): ?array
        {
            $cookies = $this->getCookies();

            return $cookies[$name] ?? null;
        }

        /** @return array<string, mixed> 
         */
        public function getParams(): array
        {
            return $this->params ??= $this->parseParams();
        }

        /**
         * @param string $key 
         * @param mixed $default 
         * @return mixed 
         */
        public function param(string $key, mixed $default = null): mixed
        {
            $params = $this->getParams();

            if ( ! isset($params[$key])) {
                return $default;
            }

            return $this->secure($params[$key]);
        }

        /** @return mixed 
         */
        public function getBody(): mixed
        {
            return $this->body ??= $this->parseBody();
        }

        /**
         * @param string $key 
         * @param mixed $default 
         * @return mixed 
         */
        public function input(string $key, mixed $default = null): mixed
        {
            /** @var null|array<string, mixed> $body
             */
            $body = $this->getBody();

            if ( ! isset($body[$key])) {
                return $default;
            }

            return $this->secure($body[$key]);
        }

        /** @return array<string, UploadedFileInterface|array<int, UploadedFileInterface>>
         */
        public function getUploadedFiles(): array
        {
            return $this->files ??= $this->parseFiles();
        }

        /**
         * @param string $name 
         * @return null|UploadedFileInterface|array<int, UploadedFileInterface>
         */
        public function getUploadedFile(string $name): null|array|UploadedFileInterface
        {
            $files = $this->getUploadedFiles();

            if ( ! isset($files[$name])) {
                return null;
            }

            return $files[$name];
        }

        /** @return array<string, mixed> 
         */
        public function getAttributes(): array
        {
            return $this->attributes;
        }

        /**
         * @param string $name 
         * @return mixed 
         */
        public function getAttribute(string $name): mixed
        {
            return $this->attributes[$name] ?? null;
        }

        /**
         * @param string $name 
         * @param mixed $value 
         * @return RequestInterface 
         */
        public function withAttribute(string $name, mixed $value): RequestInterface
        {
            $this->attributes[$name] = $value;

            return $this;
        }

        /**
         * @param string $name 
         * @return RequestInterface 
         */
        public function withoutAttribute(string $name): RequestInterface
        {
            unset($this->attributes[$name]);

            return $this;
        }


        /** @return string 
         */
        protected function parseMethod(): string
        {
            $method = $_SERVER['REQUEST_METHOD'] ?? null;

            if ( ! is_string($method) || empty($method)) {
                return 'get';
            }

            return strtolower($method);
        }

        /** @return UriInterface 
         */
        protected function parseUri(): UriInterface
        {
            $url = $_SERVER['REQUEST_URI'] ?? null;

            if ( ! is_string($url) || empty($url)) {
                return Uri::fromUrl('/');
            }

            return Uri::fromUrl($url);
        }

        /** @return array<string, string> 
         */
        protected function parseHeaders(): array
        {
            $headers = [];
            $special = ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'];

            foreach ($_SERVER as $key => $value) {
                if ( ! is_string($value)) {
                    continue;
                }

                if (str_starts_with($key, 'HTTP_')) {
                    $headers[$this->normalizeHeaderName(substr($key, 5))] = $value;
                } elseif (in_array($key, $special, true)) {
                    $headers[$this->normalizeHeaderName($key)] = $value;
                }
            }

            return $headers;
        }

        /** @return array<string, array<string, mixed>>
         */
        protected function parseCookies(): array
        {
            return [];
        }

        /** @return array<string, mixed>
         */
        protected function parseParams(): array
        {
            return $_GET;
        }

        /** @return array<string, mixed>
         */
        protected function parseBody(): array
        {
            $contentType = $this->getHeader('CONTENT_TYPE') ?? '';

            if (str_contains($contentType, 'application/json')) {
                $raw = file_get_contents('php://input') ?: '';
                $json = json_decode($raw, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return ['__ERROR__' => json_last_error_msg()];
                }

                return is_array($json) ? $json : [];
            }

            return $_POST;
        }

        /** @return array<string, UploadedFileInterface|array<int, UploadedFileInterface>>
         */
        protected function parseFiles(): array
        {
            /** @var array<string, UploadedFileInterface|array<int, UploadedFileInterface>>
             */
            $files = [];

            foreach ($_FILES as $field => $file) {
                if ( ! is_array($file)) {
                    continue;
                }

                $name = $file['name'] ?? null;
                $type = $file['type'] ?? null;
                $tmpName = $file['tmp_name'] ?? null;
                $size = $file['size'] ?? null;
                $error = $file['error'] ?? null;

                if (
                       is_array($name)
                    && is_array($type)
                    && is_array($tmpName)
                    && is_array($size)
                    && is_array($error)
                ) {
                    $count = count($name);
                    $files[$field] = [];

                    for ($i = 0; $i < $count; $i++) {
                        if (
                               ! is_string($name[$i])
                            || ! is_string($type[$i])
                            || ! is_string($tmpName[$i])
                            || ! is_int($size[$i])
                            || ! is_int($error[$i])
                        ) {
                            continue;
                        }

                        $files[$field][] = new UploadedFile(
                            $name[$i],
                            $type[$i],
                            $tmpName[$i],
                            $size[$i],
                            $error[$i],
                        );
                    }

                    continue;
                }

                if (
                       ! is_string($name)
                    || ! is_string($type)
                    || ! is_string($tmpName)
                    || ! is_int($size)
                    || ! is_int($error)
                ) {
                    continue;
                }

                $files[$field] = new UploadedFile(
                    $name,
                    $type,
                    $tmpName,
                    $size,
                    $error,
                );
            }

            return $files;
        }

        /**
         * @param mixed $value 
         * @return mixed 
         */
        protected function secure(mixed $value): mixed
        {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $value[$k] = $this->secure($v);
                }

                return $value;
            }

            if (is_string($value)) {
                $value = trim($value);
                $value = filter_var(
                    $value,
                    FILTER_UNSAFE_RAW,
                    FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
                );
            }

            return $value;
        }
    }
