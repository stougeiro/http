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
    }
