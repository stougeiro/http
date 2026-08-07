<?php declare(strict_types=1);

    namespace STDW\Http\Spec;


    trait CookiesTrait
    {
        /**
         * @var array<string, array{
         *     name: string,
         *     value: string,
         *     options: array{
         *         expires?: int,
         *         path?: string,
         *         domain?: string,
         *         secure?: bool,
         *         httponly?: bool,
         *         samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         *     }
         * }>
         */
        protected array $cookies = [];


        /**
         * @return array<string, array{
         *     name: string,
         *     value: string,
         *     options: array{
         *         expires?: int,
         *         path?: string,
         *         domain?: string,
         *         secure?: bool,
         *         httponly?: bool,
         *         samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         *     }
         * }>
         */
        public function getCookies(): array
        {
            return $this->cookies;
        }

        /**
         * @param string $name
         * @return null|array{
         *     name: string,
         *     value: string,
         *     options: array{
         *         expires?: int,
         *         path?: string,
         *         domain?: string,
         *         secure?: bool,
         *         httponly?: bool,
         *         samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         *     }
         * }
         */
        public function getCookie(string $name): ?array
        {
            return $this->cookies[$name] ?? null;
        }

        /**
         * @param string $name 
         * @param string $value 
         * @param array{
         *     expires?: int,
         *     path?: string,
         *     domain?: string,
         *     secure?: bool,
         *     httponly?: bool,
         *     samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         * } $options 
         * @return ResponseInterface 
         */
        public function withCookie(string $name, string $value, array $options = []): ResponseInterface
        {
            $this->cookies[$name] = [
                'name' => $name,
                'value' => $value,
                'options' => $this->normalizeCookieOptions($options),
            ];

            return $this;
        }

        /**
         * @param string $name
         * @return ResponseInterface
         */
        public function withoutCookie(string $name): ResponseInterface
        {
            unset($this->cookies[$name]);

            return $this;
        }

        /** @return ResponseInterface
         */
        public function clearCookies(): ResponseInterface
        {
            $this->cookies = [];

            return $this;
        }


        /**
         * Normalizes cookie options ensuring a complete shape.
         *
         * @param array{
         *     expires?: int,
         *     path?: string,
         *     domain?: string,
         *     secure?: bool,
         *     httponly?: bool,
         *     samesite?: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         * } $options 
         * @return array{
         *     expires: int,
         *     path: string,
         *     domain: string,
         *     secure: bool,
         *     httponly: bool,
         *     samesite: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         * }
         */
        protected function normalizeCookieOptions(array $options): array
        {
            return [
                'expires' => $options['expires'] ?? 0,
                'path' => $options['path'] ?? '/',
                'domain' => $options['domain'] ?? '',
                'secure' => $options['secure'] ?? false,
                'httponly' => $options['httponly'] ?? false,
                'samesite' => $options['samesite'] ?? 'strict',
            ];
        }

        /**
         * @param array{
         *     name: string,
         *     value: string,
         *     options: array{
         *         expires: int,
         *         path: string,
         *         domain: string,
         *         secure: bool,
         *         httponly: bool,
         *         samesite: 'Lax'|'lax'|'None'|'none'|'Strict'|'strict'
         *     }
         * } $cookie
         */
        protected function sendCookie(array $cookie): void
        {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                $cookie['options']
            );
        }
    }
